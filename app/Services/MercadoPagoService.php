<?php

namespace App\Services;

use App\Models\Studio;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Payment;
use App\Models\SubscriptionPlan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\EnrollmentService;
use App\Services\PricingService;
use App\Services\PayrollService; // <-- Importante para el pago a profesores
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Payment\PaymentRefundClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use Carbon\Carbon;

class MercadoPagoService
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * Configura el token global del SDK.
     */
    private function setToken(string $token): void
    {
        MercadoPagoConfig::setAccessToken($token);
    }

    /**
     * Asegura que una URL sea absoluta. Si route() devuelve una ruta relativa,
     * la prefija con app.url.
     */
    private function absoluteUrl(string $url): string
    {
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return $url;
        }
        return rtrim(config('app.url'), '/') . '/' . ltrim($url, '/');
    }

    public function createPreference($studioId, $selections, $user)
    {
        $studio = Studio::with('user.country', 'subscriptionPlan')->findOrFail($studioId);
        
        $studioToken = $studio->mp_access_token;

        // =========================================================================
        // BLINDAJE ABSOLUTO: Sin token del estudio, no hay transacción.
        // =========================================================================
        if (empty($studioToken)) {
            \Illuminate\Support\Facades\Log::critical("Intento de pago rechazado: El estudio ID {$studioId} no tiene cuenta de Mercado Pago vinculada.");
            throw new \Exception("Este estudio aún no está habilitado para recibir pagos online. Por favor, contacta a la administración.");
        }

        // Fijamos el token estrictamente al del estudio
        $this->setToken($studioToken);

        $items = [];
        $totalAmount = 0;
        $selectionsByStudent = collect($selections)->groupBy('student_id');

        foreach ($selectionsByStudent as $studentId => $selectionItems) {
            $student = Student::withoutGlobalScopes()->find($studentId);
            if (!$student) continue;

            $checkedSessionIds = $selectionItems->pluck('session_id')->toArray();
            
            $result = $this->pricingService->calculateCart($studioId, $checkedSessionIds, $student->id);

            if ($result['total'] > 0) {
                $totalAmount += $result['total'];

                $items[] = [
                    'title'       => 'Reserva Clases - ' . $student->first_name,
                    'quantity'    => 1,
                    'unit_price'  => (float) $result['total'],
                    'currency_id' => $studio->currency_code,
                ];
            }
        }

        $client = new PreferenceClient();
        
        // Obtenemos el dominio de webhook (Ngrok en local, App URL en producción)
        $webhookDomain = config('services.mercadopago.webhook_domain') ?: rtrim(config('app.url'), '/');
        $baseUrl = rtrim(config('app.url'), '/');

        $request = [
            'items'              => $items,
            'statement_descriptor' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $studio->name), 0, 20)),
            'external_reference' => json_encode([
                'user_id'    => $user->id,
                'selections' => $selections,
                'studio_id'  => $studioId,
            ]),
            'back_urls' => [
                'success' => $baseUrl . '/pagos/exito',
                'failure' => $baseUrl . '/pagos/error',
                'pending' => $baseUrl . '/pagos/pendiente',
            ],
            'auto_return'      => 'approved',
            'notification_url' => rtrim($webhookDomain, '/') . '/api/webhooks/mercadopago', 
        ];

        // =========================================================================
        // LÓGICA DE SPLIT PAYMENT (COBRO DE TU COMISIÓN)
        // =========================================================================
        $plan = $studio->subscriptionPlan;
        $feePercent = $plan ? (float) $plan->platform_fee_percent : 5.00; 

        if ($feePercent > 0 && $totalAmount > 0) {
            $feeAmount = round($totalAmount * ($feePercent / 100));
            
            // Medida de seguridad: La comisión nunca puede ser mayor o igual al total
            if ($feeAmount > 0 && $feeAmount < $totalAmount) {
                $request['marketplace_fee'] = $feeAmount;
            }
        }
        
        $preference = $client->create($request);

        if (!$preference->init_point) {
            throw new \Exception("Error al generar el link de pago con Mercado Pago.");
        }

        return [
            'init_point'    => $preference->init_point,
            'preference_id' => $preference->id,
        ];
    }

    // =========================================================================
    // EL DIRECTOR DE ORQUESTA (Dispatcher)
    // =========================================================================
    public function handlePaymentWebhook($dataId)
    {
        $this->setToken(config('services.mercadopago.token'));

        $client = new PaymentClient();
        try {
            $mpPayment = $client->get((int) $dataId);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            Log::error("MercadoPago API Error: No se pudo obtener el pago {$dataId}. Detalles: " . $e->getMessage());
            return;
        }

        if (!$mpPayment || $mpPayment->status !== 'approved') {
            Log::info("Pago {$dataId} ignorado o no aprobado. Estado: " . ($mpPayment->status ?? 'null'));
            return;
        }

        $meta = json_decode($mpPayment->external_reference, true);
        $type = $meta['type'] ?? 'student_payment'; // Fallback a alumno si no hay flag

        // Derivamos según el tipo de pago
        switch ($type) {
            case 'teacher_payment':
                $this->processTeacherPayment($meta);
                break;
            
            case 'student_payment':
                $this->processStudentPayment($dataId, $mpPayment, $meta);
                break;

            default:
                Log::warning("Tipo de pago desconocido en Webhook: {$type}");
                break;
        }
    }

    // =========================================================================
    // LÓGICA AISLADA: PAGO A PROFESORES
    // =========================================================================
    private function processTeacherPayment(array $meta)
    {
        $teacherId = $meta['teacher_id'] ?? null;
        $monthYear = $meta['month_year'] ?? null;

        if ($teacherId && $monthYear) {
            // 🚨 GUARDIA DE CONCURRENCIA EXTREMA: Bloqueo Atómico en RAM
            $lockKey = "teacher_payment_lock_{$teacherId}_{$monthYear}";
            
            // Cache::add hace la comprobación y el bloqueo en 1 solo paso indivisible.
            // Devuelve 'false' si otro hilo milisegundos más rápido ya tomó el control.
            if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 60)) {
                Log::info("Idempotencia Concurrente: Liquidación del profesor {$teacherId} para {$monthYear} ya en proceso. Ignorando clon.");
                return;
            }

            try {
                $payrollService = app(PayrollService::class);
                $payrollService->markPaymentAsPaid((int) $teacherId, $monthYear);
                Log::info("Liquidación de profesor {$teacherId} para el mes {$monthYear} procesada exitosamente vía Webhook.");
            } catch (\Exception $e) {
                // Si falla por un error real, liberamos el candado para que MP pueda reintentar
                \Illuminate\Support\Facades\Cache::forget($lockKey);
                throw $e;
            }
        }
    }

    // =========================================================================
    // 3. LÓGICA AISLADA: PAGO DE ALUMNOS (CON IDEMPOTENCIA PARA SPLIT PAYMENTS)
    // =========================================================================
    private function processStudentPayment($dataId, $mpPayment, array $meta)
    {
        // 🚨 GUARDIA DE CONCURRENCIA EXTREMA: Bloqueo Atómico en RAM
        $lockKey = "mp_payment_lock_{$dataId}";

        // Cache::add solo devuelve 'true' si la llave NO existía y logra crearla.
        // Si devuelve 'false', significa que otro webhook idéntico entró hace milisegundos.
        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 60)) {
            Log::info("Idempotencia Concurrente: El pago MP {$dataId} ya está siendo procesado por otro hilo. Abortando clon.");
            return;
        }

        try {
            // Guardia de Idempotencia Histórica (Por si Mercado Pago reintenta horas después)
            $existingPayment = Payment::where('mp_payment_id', $dataId)->first();
            
            if ($existingPayment) {
                Log::info("Idempotencia Histórica: El pago MP {$dataId} ya existe en el Ledger con estado [{$existingPayment->status}]. Cancelando clon.");
                return; 
            }

            $selectionsPagadas = $meta['selections'] ?? [];
            $studioId = $meta['studio_id'] ?? null;

            if (empty($selectionsPagadas)) {
                Log::warning("Pago {$dataId} sin selections en external_reference");
                return;
            }

            $totalAmount = (float) $mpPayment->transaction_amount;

            // MAGIA FINANCIERA: Extraer tu comisión exacta reportada por Mercado Pago
            $totalPlatformFee = 0;
            if (isset($mpPayment->fee_details)) {
                foreach ($mpPayment->fee_details as $fee) {
                    if ($fee->type === 'application_fee') { 
                        $totalPlatformFee = (float) $fee->amount;
                    }
                }
            }

            $numSelections = count($selectionsPagadas);
            $amountPerSelection = $numSelections > 0 ? round($totalAmount / $numSelections, 2) : 0;
            $platformFeePerSelection = $numSelections > 0 ? round($totalPlatformFee / $numSelections, 2) : 0;

            $selectionsByStudent = collect($selectionsPagadas)->groupBy('student_id');

            // CAPA 4: PRE-TRANSACTION CAPACITY VALIDATION (All-or-Nothing)
            $allSessionIds = collect($selectionsPagadas)->pluck('session_id')->unique()->toArray();
            $enrollmentService = app(EnrollmentService::class);
            $capacityInfo = $enrollmentService->getCapacityInfo($allSessionIds);

            $spotsRequested = [];
            foreach ($selectionsPagadas as $sel) {
                $sid = $sel['session_id'];
                $spotsRequested[$sid] = ($spotsRequested[$sid] ?? 0) + 1;
            }

            $overbookedSessions = [];
            foreach ($spotsRequested as $sid => $requested) {
                $available = $capacityInfo[$sid]['available_spots'] ?? 0;
                if ($requested > $available) {
                    $overbookedSessions[] = [
                        'session_id' => $sid,
                        'requested'  => $requested,
                        'available'  => $available,
                    ];
                }
            }

            if (!empty($overbookedSessions)) {
                $overIds = array_column($overbookedSessions, 'session_id');
                Log::warning("Layer 4 bloqueo: sessions " . implode(',', $overIds) . " overbooked. Reembolsando payment {$dataId} completo.");

                $firstSel = $selectionsPagadas[0];
                $firstSession = ClassSession::withoutGlobalScopes()->find($firstSel['session_id']);
                $firstStudent = Student::withoutGlobalScopes()->find($firstSel['student_id']);
                $studio = $studioId ? Studio::withoutGlobalScopes()->find($studioId) : ($firstSession ? $firstSession->workshop->studio : null);

                $payment = Payment::create([
                    'student_id'     => $firstStudent->id ?? 0,
                    'studio_id'      => $firstSession->studio_id ?? $studioId,
                    'workshop_id'    => $firstSession->workshop_id ?? null,
                    'payment_type'   => count($selectionsPagadas) == 1 ? 'single' : 'pack',
                    'payment_method' => 'mercadopago',
                    'amount'         => $totalAmount,
                    'platform_fee'   => $totalPlatformFee,
                    'mp_payment_id'  => $dataId,
                    'status'         => 'refunded_overbooking',
                ]);

                $allPivot = [];
                foreach ($selectionsPagadas as $sel) {
                    $allPivot[$sel['session_id']] = ['student_id' => $sel['student_id']];
                }
                $payment->classSessions()->attach($allPivot);

                try {
                    $studioToken = $studio?->mp_access_token;
                    if ($studioToken) {
                        $this->setToken($studioToken);
                    } else {
                        $this->setToken(config('services.mercadopago.token'));
                    }
                    $refundClient = new PaymentRefundClient();
                    $refund = $refundClient->refundTotal((int) $dataId);
                    Log::info("Layer 4 reembolso: payment {$dataId}, refund status {$refund->status}");
                } catch (\Exception $e) {
                    Log::error("Layer 4 error reembolso MP: payment {$dataId}, " . $e->getMessage());
                }

                try {
                    if ($firstStudent && $firstStudent->user && $firstSession) {
                        Mail::to($firstStudent->user->email)
                            ->queue(new \App\Mail\ClassRefundedMail(
                                $firstSession,
                                $firstStudent,
                                $studio,
                                $totalAmount
                            ));
                    }
                } catch (\Exception $e) {
                    Log::error('Layer 4 error enviando ClassRefundedMail: ' . $e->getMessage());
                }

                return;
            }

            DB::beginTransaction();
            
            $studio = $studioId ? Studio::withoutGlobalScopes()->find($studioId) : null;

            foreach ($selectionsByStudent as $studentId => $sels) {
                $firstSel = $sels->first();
                $sessionIds = $sels->pluck('session_id')->toArray();
                $firstSession = ClassSession::withoutGlobalScopes()->find($firstSel['session_id']);

                if (!$firstSession) continue;

                $student = Student::withoutGlobalScopes()->find($studentId);
                if (!$student) continue;

                $paymentAmount = $amountPerSelection * count($sessionIds);
                $paymentFee = $platformFeePerSelection * count($sessionIds);

                $payment = Payment::create([
                    'student_id'     => $studentId,
                    'studio_id'      => $firstSession->studio_id ?? $studioId,
                    'workshop_id'    => $firstSession->workshop_id,
                    'payment_type'   => count($sessionIds) == 1 ? 'single' : 'pack',
                    'payment_method' => 'mercadopago',
                    'amount'         => $paymentAmount,
                    'platform_fee'   => $paymentFee,
                    'mp_payment_id'  => $dataId,
                    'status'         => 'approved',
                ]);

                $pivotData = [];
                foreach ($sessionIds as $sid) {
                    $pivotData[$sid] = ['student_id' => $studentId];
                }
                $payment->classSessions()->attach($pivotData);

                $pivotActual = DB::table('class_session_student')
                    ->where('class_session_id', $sessionIds[0] ?? 0)
                    ->where('student_id', $studentId)
                    ->first();

                $yaEstabaPagado = $pivotActual && $pivotActual->payment_status === 'paid';

                if (!$yaEstabaPagado) {
                    $overbookedGroup = false;

                    foreach ($sessionIds as $sid) {
                        $session = ClassSession::withoutGlobalScopes()->find($sid);
                        if (!$session) continue;

                        $maxStudents = $session->max_students;

                        $updated = DB::update("
                            UPDATE class_session_student
                            SET payment_status = 'paid', updated_at = ?
                            WHERE class_session_id = ?
                              AND student_id = ?
                              AND payment_status = 'pending'
                              AND (
                                  SELECT COUNT(*) FROM (
                                      SELECT 1 FROM class_session_student
                                      WHERE class_session_id = ?
                                        AND payment_status = 'paid'
                                  ) AS _cnt
                              ) < ?
                        ", [now(), $sid, $studentId, $sid, $maxStudents]);

                        if ($updated === 0) {
                            $alreadyPaid = DB::table('class_session_student')
                                ->where('class_session_id', $sid)
                                ->where('student_id', $studentId)
                                ->where('payment_status', 'paid')
                                ->exists();

                            if (!$alreadyPaid) {
                                $overbookedGroup = true;
                                Log::warning("Overbooking detectado en BD: session {$sid}, student {$studentId}, payment {$dataId}");
                                break;
                            }
                        } else {
                            $session->attendances()->firstOrCreate([
                                'student_id' => $studentId,
                                'studio_id'  => $session->studio_id ?? $studioId 
                            ]);
                        }
                    }

                    if ($overbookedGroup) {
                        $payment->update(['status' => 'refunded_overbooking']);

                        try {
                            $studioToken = $studio?->mp_access_token;
                            if ($studioToken) {
                                $this->setToken($studioToken);
                            } else {
                                $this->setToken(config('services.mercadopago.token'));
                            }

                            $refundClient = new PaymentRefundClient();
                            $refund = $refundClient->refundTotal((int) $dataId);
                            Log::info("Overbooking resuelto: reembolso emitido para payment {$dataId}, status {$refund->status}");
                        } catch (\Exception $e) {
                            Log::error("Error al emitir reembolso MP por overbooking en BD: payment {$dataId}, " . $e->getMessage());
                        }

                        try {
                            if ($student->user && isset($firstSession)) {
                                $studioForMail = $studio ?? $firstSession->workshop->studio;
                                Mail::to($student->user->email)
                                    ->queue(new \App\Mail\ClassRefundedMail(
                                        $firstSession,
                                        $student,
                                        $studioForMail,
                                        $paymentAmount
                                    ));
                            }
                        } catch (\Exception $e) {
                            Log::error('Error enviando ClassRefundedMail: ' . $e->getMessage());
                        }

                    } else {
                        try {
                            if ($student->user) {
                                $student->user->notify(new \App\Notifications\StudentPaymentApprovedNotification($payment));
                            }
                        } catch (\Exception $e) {
                            Log::error('Error notificando pago aprobado MP: ' . $e->getMessage());
                        }
                    }

                } else {
                    Log::info("Pago {$dataId} ya estaba registrado. Evitando duplicidad cruzada.");
                }
            }

            DB::commit();

            $affectedSessionIds = collect($selectionsPagadas)->pluck('session_id')->unique()->toArray();
            app(EnrollmentService::class)->notifyCapacityChange($affectedSessionIds, 0);

        } catch (\Exception $e) {
            DB::rollBack();
            // Si hubo un error técnico real, liberamos el candado para permitir que Mercado Pago reintente
            \Illuminate\Support\Facades\Cache::forget($lockKey);
            Log::error("Error asignando pago {$dataId} en BD: " . $e->getMessage());
            throw $e;
        }
    }

    public function processSaaSSubscription(int $dataId): void
    {
        $subscription = $this->getSubscriptionDetails($dataId);
                
        $studioId = $subscription['external_reference'] ?? null;
        $status = $subscription['status'] ?? null;

        if ($studioId && $studio = Studio::with('subscriptionPlan')->find($studioId)) {
            
            // 🚨 GUARDIA DE IDEMPOTENCIA: Verificar si la suscripción se procesó en los últimos 30 segundos
            // Esto evita sumar 'billing_cycles_count' múltiples veces por webhooks simultáneos.
            if ($studio->updated_at && $studio->updated_at->diffInSeconds(now()) < 30) {
                Log::info("Idempotencia Activa: Suscripción SaaS para estudio {$studioId} actualizada recientemente. Ignorando webhook clonado.");
                return;
            }

            if ($status === 'authorized') {
                
                $plan = $studio->subscriptionPlan;
                $planName = $plan ? $plan->name : 'Pro';
                $planSlug = $plan ? $plan->slug : 'pro';
                $planPrice = $plan ? $plan->price : 45000;

                $currentExpiration = $studio->subscription_expires_at;
                
                if ($currentExpiration && $studio->subscription_status !== 'free') {
                    $newExpiration = Carbon::parse($currentExpiration)->addMonth();
                } else {
                    $newExpiration = now()->addMonth();
                }

                $currentCycle = $studio->billing_cycles_count + 1;

                $studio->update([
                    'mp_preapproval_id'       => $dataId,
                    'subscription_status'     => $planSlug,
                    'subscription_expires_at' => $newExpiration,
                    'billing_cycles_count'    => $currentCycle,
                ]);

                if ($plan && $plan->max_billing_cycles && $currentCycle >= $plan->max_billing_cycles) {
                    
                    try {
                        $this->cancelPreapproval($dataId);
                    } catch (\Exception $e) {
                        Log::error("Error cancelando suscripción SaaS en MP: " . $e->getMessage());
                    }
                    
                    Mail::to($studio->user->email)
                        ->queue(new \App\Mail\PlanLifecycleEndingMail($studio));
                        
                } else {
                    Mail::to($studio->user->email)
                        ->queue(new \App\Mail\SubscriptionReceiptMail($studio, $planName, $planPrice));
                }

                $adminEmail = env('ADMIN_NOTIFICATION_EMAIL', 'admin@estadoprisma.test');
                Mail::to($adminEmail)
                    ->queue(new \App\Mail\NewSubscriptionAlertMail($studio, $planName));

            } elseif (in_array($status, ['paused', 'cancelled'])) {
                $studio->update(['mp_preapproval_id' => null]);
            }

            try {
                if ($studio->user) {
                    $studio->user->notify(new \App\Notifications\SaaSSubscriptionNotification($studio, $status));
                }
            } catch (\Exception $e) {
                Log::error('Error registrando notificación in-app de suscripción SaaS: ' . $e->getMessage());
            }
        }
    }

    public function createSubscriptionLink(Studio $studio, string $planSlug, int $countryId): string
    {
        $plan = SubscriptionPlan::where('slug', $planSlug)
            ->where('is_active', true)
            ->firstOrFail();

        $country = \App\Models\Country::find($countryId);
        if (!$country) {
            Log::error('País no encontrado al crear link de suscripción', [
                'studio_id'  => $studio->id,
                'country_id' => $countryId,
            ]);
            throw new \Exception('El país seleccionado no es válido. Por favor, elige otro.');
        }
        $currencyCode = $country->currency_code ?: 'CLP';

        // 1. Validación estricta de cupos (Capacity Limit)
        if ($plan->capacity_limit !== null) {
            $currentSubscribers = Studio::where('subscription_plan_id', $plan->id)
                ->whereIn('subscription_status', ['pro', 'elite', 'past_due'])
                ->count();

            if ($currentSubscribers >= $plan->capacity_limit) {
                throw new \Exception('Lo sentimos, los cupos para este plan se han agotado.');
            }
        }

        // 2. Generar link de pago vía suscripción (Preapproval) — la API se llama PRIMERO
        $this->setToken(config('services.mercadopago.token'));

        $studio->loadMissing('user');

        $client = new PreApprovalClient();

        $request = [
            'reason'             => $plan->name,
            'external_reference' => (string) $studio->id,
            'payer_email'        => $studio->user->email,
            'auto_recurring'     => [
                'frequency'          => 1,
                'frequency_type'     => 'months',
                'transaction_amount' => (float) $plan->price,
                'currency_id'        => $currencyCode,
            ],
            'back_url' => $this->absoluteUrl(route('dashboard', ['subdomain' => $studio->subdomain])),
        ];

        try {
            $preapproval = $client->create($request);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $responseContent = $apiResponse ? $apiResponse->getContent() : null;

            Log::error('MercadoPago API Error al crear preapproval', [
                'studio_id'       => $studio->id,
                'plan_slug'       => $planSlug,
                'country_id'      => $countryId,
                'currency_code'   => $currencyCode,
                'api_http_status' => $e->getStatusCode(),
                'api_message'     => $e->getMessage(),
                'api_response'    => $responseContent,
            ]);
            throw new \Exception('El servicio de pagos no está disponible en este momento. Por favor, intenta más tarde.');
        }

        if (!$preapproval->init_point) {
            throw new \Exception('No se pudo generar el link de suscripción. Intenta más tarde.');
        }

        // 3. Auditoría: SOLO después de crear exitosamente el preapproval en MP,
        //    actualizamos el plan del estudio. Así garantizamos integridad transaccional.
        if ($studio->subscription_plan_id !== $plan->id) {
            $studio->update([
                'subscription_plan_id'  => $plan->id,
                'billing_cycles_count'  => 0,
            ]);
        }

        return $preapproval->init_point;
    }

    public function getSubscriptionDetails($dataId)
    {
        $this->setToken(config('services.mercadopago.token'));

        $client = new PreApprovalClient();
        $preapproval = $client->get($dataId);

        if (!$preapproval) {
            throw new \Exception("Suscripción {$dataId} no encontrada en Mercado Pago.");
        }

        return [
            'external_reference' => $preapproval->external_reference,
            'status'             => $preapproval->status,
        ];
    }

    public function createTeacherPaymentPreference(array $data): array
    {
        $this->setToken($data['teacher_mp_token']);

        // Moneda del estudio (país del dueño)
        $studio = Studio::with('user.country')->find($data['studio_id']);
        $currencyCode = $studio ? $studio->currency_code : 'CLP';

        $client = new PreferenceClient();
        
        // Obtenemos el dominio dinámico
        $webhookDomain = config('services.mercadopago.webhook_domain') ?: rtrim(config('app.url'), '/');

        $request = [
            'items' => [
                [
                    'title'       => $data['title'],
                    'quantity'    => 1,
                    'unit_price'  => (float) $data['amount'],
                    'currency_id' => $currencyCode,
                ],
            ],
            'external_reference' => json_encode([
                'teacher_id' => $data['teacher_id'],
                'studio_id'  => $data['studio_id'],
                'month_year' => $data['month_year'],
                'amount'     => $data['amount'],
                'type'       => 'teacher_payment', // <-- Flag clave
            ]),
            'back_urls' => [
                'success' => $this->absoluteUrl($data['success_url']),
                'failure' => $this->absoluteUrl($data['failure_url']),
                'pending' => $this->absoluteUrl($data['failure_url']),
            ],
            'auto_return' => 'approved',
            'notification_url' => rtrim($webhookDomain, '/') . '/api/webhooks/mercadopago', // <-- Blindaje de webhook agregado
        ];

        $preference = $client->create($request);

        if (!$preference->init_point) {
            throw new \Exception("Error al generar el link de pago con Mercado Pago.");
        }

        return [
            'init_point'    => $preference->init_point,
            'preference_id' => $preference->id,
        ];
    }

    public function cancelPreapproval(string $preapprovalId): void
    {
        $this->setToken(config('services.mercadopago.token'));
        
        $client = new \MercadoPago\Client\PreApproval\PreApprovalClient();
        
        $client->update($preapprovalId, [
            "status" => "cancelled"
        ]);
        
        \Illuminate\Support\Facades\Log::info("Suscripción {$preapprovalId} cancelada exitosamente vía API.");
    }
}