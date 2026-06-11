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
use App\Services\PricingService;
use App\Services\PayrollService; // <-- Importante para el pago a profesores
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
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
            $payrollService = app(PayrollService::class);
            $payrollService->markPaymentAsPaid((int) $teacherId, $monthYear);
            Log::info("Liquidación de profesor {$teacherId} para el mes {$monthYear} procesada exitosamente vía Webhook.");
        }
    }

    // =========================================================================
    // 3. LÓGICA AISLADA: PAGO DE ALUMNOS (CON IDEMPOTENCIA PARA SPLIT PAYMENTS)
    // =========================================================================
    private function processStudentPayment($dataId, $mpPayment, array $meta)
    {
        $selectionsPagadas = $meta['selections'] ?? [];
        $studioId = $meta['studio_id'] ?? null;

        if (empty($selectionsPagadas)) {
            Log::warning("Pago {$dataId} sin selections en external_reference");
            return;
        }

        $totalAmount = (float) $mpPayment->transaction_amount;

        // =========================================================================
        // MAGIA FINANCIERA: Extraer tu comisión exacta reportada por Mercado Pago
        // =========================================================================
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

        DB::beginTransaction();
        try {
            foreach ($selectionsByStudent as $studentId => $sels) {
                $firstSel = $sels->first();
                $sessionIds = $sels->pluck('session_id')->toArray();
                $firstSession = ClassSession::withoutGlobalScopes()->find($firstSel['session_id']);

                if (!$firstSession) continue;

                $student = Student::withoutGlobalScopes()->find($studentId);
                if (!$student) continue;

                // Cálculos finales para la fila del pago
                $paymentAmount = $amountPerSelection * count($sessionIds);
                $paymentFee = $platformFeePerSelection * count($sessionIds);

                // 1. REGISTRO FINANCIERO (Siempre se ejecuta para que cuadre el Ledger)
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

                // 2. LÓGICA DE IDEMPOTENCIA Y NOTIFICACIONES
                // Revisamos si la primera sesión de este grupo ya estaba pagada en la BD
                $pivotActual = DB::table('class_session_student')
                    ->where('class_session_id', $sessionIds[0] ?? 0)
                    ->where('student_id', $studentId)
                    ->first();

                $yaEstabaPagado = $pivotActual && $pivotActual->payment_status === 'paid';

                // Solo actualizamos cupos y disparamos notificaciones si es el primer Webhook de la compra
                if (!$yaEstabaPagado) {
                    
                    foreach ($sessionIds as $sid) {
                        $session = ClassSession::withoutGlobalScopes()->find($sid);
                        if ($session) {
                            $session->students()
                                ->withoutGlobalScopes()
                                ->updateExistingPivot($studentId, ['payment_status' => 'paid']);

                            $session->attendances()->firstOrCreate(['student_id' => $studentId]);
                        }
                    }

                    try {
                        if ($student->user) {
                            $student->user->notify(new \App\Notifications\StudentPaymentApprovedNotification($payment));
                        }
                    } catch (\Exception $e) {
                        Log::error('Error notificando pago aprobado MP: ' . $e->getMessage());
                    }

                } else {
                    // Silenciamos la notificación pero dejamos constancia en el log
                    Log::info("Pago dividido detectado (MP ID: {$dataId}). Registro financiero guardado exitosamente. Se omitió la inscripción doble y el correo para evitar duplicados a la alumna.");
                }
            }

            DB::commit();

            // 3. LÓGICA DE LLENADO DE CUPOS Y NOTIFICACIÓN A LISTA DE ESPERA
            $affectedSessionIds = collect($selectionsPagadas)->pluck('session_id')->unique();
            foreach ($affectedSessionIds as $sid) {
                $session = ClassSession::withoutGlobalScopes()
                    ->with(['workshop' => fn($q) => $q->withoutGlobalScopes(), 'schedule'])
                    ->find($sid);

                if (!$session) continue;

                $maxStudents = $session->max_students;
                $paidCount = DB::table('class_session_student')
                    ->where('class_session_id', $sid)
                    ->where('payment_status', 'paid')
                    ->count();

                if ($paidCount >= $maxStudents) {
                    $pendingStudentIds = DB::table('class_session_student')
                        ->where('class_session_id', $sid)
                        ->where('payment_status', 'pending')
                        ->pluck('student_id');

                    if ($pendingStudentIds->isNotEmpty()) {
                        $pendingUsers = \App\Models\User::whereHas('studentProfiles', function ($q) use ($pendingStudentIds) {
                            $q->withoutGlobalScopes()->whereIn('id', $pendingStudentIds);
                        })->get();

                        foreach ($pendingUsers as $user) {
                            try {
                                $user->notify(new \App\Notifications\ClassFullNotification($session));
                            } catch (\Exception $e) {
                                Log::error('Error enviando ClassFullNotification desde MP: ' . $e->getMessage());
                            }
                        }
                    }
                }
            }

        } catch (\Exception $e) {
            DB::rollBack();
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
            
            if ($status === 'authorized') {
                
                // 0. Cargar el plan dinámicamente ANTES de hacer nada
                $plan = $studio->subscriptionPlan;
                $planName = $plan ? $plan->name : 'Pro';
                $planSlug = $plan ? $plan->slug : 'pro';
                $planPrice = $plan ? $plan->price : 45000;

                // 1. Lógica Anti-Deslizamiento de Facturación
                $currentExpiration = $studio->subscription_expires_at;
                
                // Verificamos si el estado actual es distinto a 'free' (puede ser 'pro', 'founder-elite', 'past_due', etc.)
                if ($currentExpiration && $studio->subscription_status !== 'free') {
                    $newExpiration = Carbon::parse($currentExpiration)->addMonth();
                } else {
                    $newExpiration = now()->addMonth();
                }

                // 2. Incrementamos el ciclo actual
                $currentCycle = $studio->billing_cycles_count + 1;

                // 3. Actualización 100% dinámica
                $studio->update([
                    'mp_preapproval_id'       => $dataId,
                    'subscription_status'     => $planSlug,
                    'subscription_expires_at' => $newExpiration,
                    'billing_cycles_count'    => $currentCycle,
                ]);

                // 4. Evaluación del Límite de Vida del Plan (Sunsetting)
                if ($plan && $plan->max_billing_cycles && $currentCycle >= $plan->max_billing_cycles) {
                    
                    // A. Cancelar cobro automático en Mercado Pago
                    try {
                        $this->cancelPreapproval($dataId);
                    } catch (\Exception $e) {
                        Log::error("Error cancelando suscripción SaaS en MP: " . $e->getMessage());
                    }
                    
                    // B. Correo de fin de beneficio
                    Mail::to($studio->user->email)
                        ->queue(new \App\Mail\PlanLifecycleEndingMail($studio));
                        
                } else {
                    // Recibo normal dinámico
                    Mail::to($studio->user->email)
                        ->queue(new \App\Mail\SubscriptionReceiptMail($studio, $planName, $planPrice));
                }

                // Alerta interna para la plataforma dinámica
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

    public function createSubscriptionLink(Studio $studio, string $planSlug): string
    {
        // Eager load para currency_code (navega user->country)
        $studio->loadMissing('user.country');

        $plan = SubscriptionPlan::where('slug', $planSlug)
            ->where('is_active', true)
            ->firstOrFail();

        // 1. Validación estricta de cupos (Capacity Limit)
        if ($plan->capacity_limit !== null) {
            $currentSubscribers = Studio::where('subscription_plan_id', $plan->id)
                ->whereIn('subscription_status', ['pro', 'elite', 'past_due'])
                ->count();

            if ($currentSubscribers >= $plan->capacity_limit) {
                throw new \Exception('Lo sentimos, los cupos para este plan se han agotado.');
            }
        }

        // 2. Auditoría: Si está cambiando a un plan distinto, reiniciamos el contador de ciclos
        if ($studio->subscription_plan_id !== $plan->id) {
            $studio->update([
                'subscription_plan_id'  => $plan->id,
                'billing_cycles_count'  => 0,
            ]);
        }

        // 3. Generar link de pago vía suscripción (Preapproval)
        $this->setToken(config('services.mercadopago.token'));

        $client = new PreApprovalClient();

        $request = [
            'reason'             => $plan->name,
            'external_reference' => (string) $studio->id,
            'auto_recurring'     => [
                'frequency'          => 1,
                'frequency_type'     => 'months',
                'transaction_amount' => (float) $plan->price,
                'currency_id'        => $studio->currency_code,
            ],
            'back_url' => $this->absoluteUrl(route('dashboard', ['subdomain' => $studio->subdomain])),
        ];

        $preapproval = $client->create($request);

        if (!$preapproval->init_point) {
            throw new \Exception('No se pudo generar el link de suscripción. Intenta más tarde.');
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