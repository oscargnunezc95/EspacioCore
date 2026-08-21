<?php

namespace App\Services;

use App\Models\Studio;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Services\EnrollmentService;
use App\Services\PricingService;
use App\Services\PayrollService;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\Payment\PaymentRefundClient;
use MercadoPago\Client\Order\OrderClient;
use Illuminate\Support\Facades\Http;
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
        $studio = Studio::with('user.country')->findOrFail($studioId);
        
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
            'items'               => $items,
            'statement_descriptor' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $studio->name), 0, 20)),
            'external_reference' => json_encode([
                'type'       => 'student_payment', // ✅ AGREGADO: Bandera obligatoria para el enrutador del webhook
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

        // NOTA: El 100% del dinero va al token del estudio sin retenciones automáticas.
        // La comisión de la plataforma se factura aparte mediante el sistema Floor-Capped.

        $preference = $client->create($request);

        if (!$preference->init_point) {
            throw new \Exception("Error al generar el link de pago con Mercado Pago.");
        }

        return [
            'init_point'    => $preference->init_point,
            'preference_id' => $preference->id,
        ];
    }

    /**
     * El Director de Orquesta (Dispatcher) del Webhook
     * Procesa la notificación de Mercado Pago aplicando inferencia de tipos y validación estricta.
     */
    public function handlePaymentWebhook($dataId): void
    {
        $this->setToken(config('services.mercadopago.token'));

        $client = new PaymentClient();
        try {
            $mpPayment = $client->get((int) $dataId);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            Log::error("MercadoPago API Error: No se pudo obtener el pago {$dataId}. Detalles: " . $e->getMessage());
            return;
        }

        // Si el pago no existe o aún no está aprobado, se ignora pacíficamente sin error
        if (!$mpPayment || $mpPayment->status !== 'approved') {
            Log::info("Pago {$dataId} ignorado o no aprobado. Estado actual: " . ($mpPayment->status ?? 'null'));
            return;
        }

        $meta = json_decode($mpPayment->external_reference, true);

        // 1. RESOLUCIÓN DE CACHÉ (Para referencias cortas del Orders API / QR Estático)
        if (!is_array($meta) || !isset($meta['type'])) {
            $cached = \Illuminate\Support\Facades\Cache::get("mp_order_meta:{$mpPayment->external_reference}");
            if (is_array($cached)) {
                $meta = $cached;
                Log::info("Metadata de orden resuelta exitosamente desde caché", ['ref' => $mpPayment->external_reference]);
            }
        }

        // 2. 🛡️ INFERENCIA AUTOMÁTICA DE TIPO (Smart Fallback para payloads legacy o incompletos)
        // Si el JSON es válido pero le falta la bandera 'type', lo deducimos por su contenido
        if (is_array($meta) && !isset($meta['type'])) {
            if (isset($meta['selections'])) {
                $meta['type'] = 'student_payment';
                Log::info("💡 [Smart Fallback] Tipo 'student_payment' inferido automáticamente por la presencia de 'selections' para pago ID: {$dataId}");
            } elseif (isset($meta['teacher_id'])) {
                $meta['type'] = 'teacher_payment';
                Log::info("💡 [Smart Fallback] Tipo 'teacher_payment' inferido automáticamente para pago ID: {$dataId}");
            } elseif (isset($meta['invoice_id'])) {
                $meta['type'] = 'platform_invoice_payment';
                Log::info("💡 [Smart Fallback] Tipo 'platform_invoice_payment' inferido automáticamente para pago ID: {$dataId}");
            }
        }

        // 3. 🚨 BLINDAJE ESTRICTO ANTE FALLOS SILENCIOSOS
        // Si después de la inferencia sigue sin haber un tipo válido, abortamos CON EXCEPCIÓN
        // para que el WebhookController no registre un falso positivo en los logs.
        if (!is_array($meta) || !isset($meta['type'])) {
            Log::warning("⚠️ [MP Webhook] Abortando: Webhook recibido sin metadata válida en external_reference y sin posible inferencia.", [
                'payment_id'         => $dataId,
                'external_reference' => $mpPayment->external_reference,
                'decoded'            => $meta,
            ]);
            
            throw new \Exception("Metadata inválida o incompleta (falta atributo 'type') para el pago MP ID: {$dataId}");
        }

        $type = $meta['type'];

        // 4. DERIVACIÓN HACIA EL SUB-SISTEMA CORRESPONDIENTE
        switch ($type) {
            case 'teacher_payment':
                $this->processTeacherPayment($meta);
                break;

            case 'student_payment':
                $this->processStudentPayment($dataId, $mpPayment, $meta);
                break;

            case 'platform_invoice_payment':
                $this->processPlatformInvoicePayment($dataId, $mpPayment, $meta);
                break;

            default:
                Log::warning("⚠️ [MP Webhook] Tipo de pago desconocido o no manejado: [{$type}] para ID: {$dataId}");
                throw new \Exception("Tipo de pago no soportado [{$type}] en Webhook para ID: {$dataId}");
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
    // 3. LÓGICA AISLADA: PAGO DE ALUMNOS (CON IDEMPOTENCIA Y MAILS GARANTIZADOS)
    // =========================================================================
    private function processStudentPayment($dataId, $mpPayment, array $meta): void
    {
        $lockKey = "mp_payment_lock_{$dataId}";

        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 60)) {
            Log::info("Idempotencia Concurrente: El pago MP {$dataId} ya está siendo procesado por otro hilo. Abortando clon.");
            return;
        }

        try {
            $existingPayment = Payment::where('mp_payment_id', $dataId)->first();
            
            if ($existingPayment) {
                Log::info("Idempotencia Histórica: El pago MP {$dataId} ya existe en el Ledger con estado [{$existingPayment->status}]. Cancelando clon.");
                \Illuminate\Support\Facades\Cache::forget($lockKey);
                return; 
            }

            // Si viene del Order API (QR), convertir session_ids + student_id → formato selections
            if (empty($meta['selections']) && !empty($meta['session_ids']) && !empty($meta['student_id'])) {
                $meta['selections'] = array_map(function ($sid) use ($meta) {
                    return ['session_id' => (int) $sid, 'student_id' => (int) $meta['student_id']];
                }, $meta['session_ids']);
            }

            $selectionsPagadas = $meta['selections'] ?? [];
            $studioId = $meta['studio_id'] ?? null;

            if (empty($selectionsPagadas)) {
                Log::warning("Pago {$dataId} sin selections en external_reference");
                \Illuminate\Support\Facades\Cache::forget($lockKey);
                return;
            }

            $totalAmount = (float) $mpPayment->transaction_amount;

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

            // =========================================================================
            // CAPA 4: PRE-VALIDACIÓN DE CAPACIDAD (All-or-Nothing Pre-DB)
            // =========================================================================
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

            // --- CASO A: OVERBOOKING DETECTADO ANTES DE ENTRAR A BD ---
            if (!empty($overbookedSessions)) {
                $overIds = array_column($overbookedSessions, 'session_id');
                Log::warning("Layer 4 bloqueo: sessions " . implode(',', $overIds) . " overbooked. Reembolsando payment {$dataId} completo.");

                $firstSel = $selectionsPagadas[0];
                $firstSession = ClassSession::with('workshop.studio')->withoutGlobalScopes()->find($firstSel['session_id']);
                $firstStudent = Student::with('user')->withoutGlobalScopes()->find($firstSel['student_id']);
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

                \Illuminate\Support\Facades\Cache::forget($lockKey);

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
                        Log::info("📧 [Mail encolado] ClassRefundedMail enviado a {$firstStudent->user->email} por overbooking Pre-DB.");
                    } else {
                        Log::warning("📧 [Mail omitido] Faltaron datos de relación (Student/User/Session) en overbooking Pre-DB para el ID {$dataId}.");
                    }
                } catch (\Exception $e) {
                    Log::error('Layer 4 error enviando ClassRefundedMail: ' . $e->getMessage());
                }

                return;
            }

            // =========================================================================
            // CAPA 3: TRANSACCIÓN FINANCIERA EN BD
            // =========================================================================
            DB::beginTransaction();
            
            $studio = $studioId ? Studio::with('user')->withoutGlobalScopes()->find($studioId) : null;
            $overbookedGroup = false;
            $createdPayments = []; 

            foreach ($selectionsByStudent as $studentId => $sels) {
                $firstSel = $sels->first();
                $sessionIds = $sels->pluck('session_id')->toArray();
                $firstSession = ClassSession::with('workshop.studio')->withoutGlobalScopes()->find($firstSel['session_id']);

                if (!$firstSession) continue;

                // =========================================================
                // 🧠 OBTENER EL MAPA DETERMINISTA DE TIERS DEL ALGORITMO
                // =========================================================
                $cartResult = $this->pricingService->calculateCart($firstSession->studio_id ?? $studioId, $sessionIds, $studentId);
                $sessionTierMap = [];
                
                if (!empty($cartResult['breakdown'])) {
                    foreach ($cartResult['breakdown'] as $bItem) {
                        $tierId = $bItem['tier_id'] ?? null;
                        if ($tierId && !empty($bItem['items'])) {
                            foreach ($bItem['items'] as $sItem) {
                                // Mapeamos cada session_id con el tier_id que lo tasó
                                $sessionTierMap[$sItem['id']] = $tierId;
                            }
                        }
                    }
                }

                $student = Student::with('user')->withoutGlobalScopes()->find($studentId);
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

                $createdPayments[] = [
                    'payment'      => $payment,
                    'student'      => $student,
                    'firstSession' => $firstSession
                ];

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
                    foreach ($sessionIds as $sid) {
                        $session = ClassSession::withoutGlobalScopes()->find($sid);
                        if (!$session) continue;

                        $maxStudents = $session->max_students;
                        $tierId = $sessionTierMap[$sid] ?? null; // ID del Pack asignado

                        // 🚀 GUARDIA DETERMINISTA: Inyección de workshop_price_id
                        $updated = DB::update("
                            UPDATE class_session_student
                            SET payment_status = 'paid', workshop_price_id = ?, updated_at = ?
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
                        ", [$tierId, now(), $sid, $studentId, $sid, $maxStudents]);

                        if ($updated === 0) {
                            $alreadyPaid = DB::table('class_session_student')
                                ->where('class_session_id', $sid)
                                ->where('student_id', $studentId)
                                ->where('payment_status', 'paid')
                                ->exists();

                            if (!$alreadyPaid) {
                                $isEnrolled = DB::table('class_session_student')
                                    ->where('class_session_id', $sid)
                                    ->where('student_id', $studentId)
                                    ->exists();

                                if (!$isEnrolled) {
                                    $paidCount = DB::table('class_session_student')
                                        ->where('class_session_id', $sid)
                                        ->where('payment_status', 'paid')
                                        ->count();

                                    if ($paidCount < $maxStudents) {
                                        // 🚀 GUARDIA DETERMINISTA: Guardado de workshop_price_id en auto-enroll
                                        DB::table('class_session_student')->insert([
                                            'class_session_id'  => $sid,
                                            'student_id'        => $studentId,
                                            'payment_status'    => 'paid',
                                            'workshop_price_id' => $tierId,
                                            'created_at'        => now(),
                                            'updated_at'        => now(),
                                        ]);
                                        
                                        $session->attendances()->firstOrCreate([
                                            'student_id' => $studentId,
                                            'studio_id'  => $session->studio_id ?? $studioId,
                                        ]);
                                        Log::info("Alumno {$studentId} auto-inscrito y pagado en session {$sid} (pago sin pre-inscripción)", [
                                            'payment_id' => $dataId,
                                            'tier_id'    => $tierId
                                        ]);
                                    } else {
                                        $overbookedGroup = true;
                                        Log::warning("Overbooking real (sin cupo) en session {$sid}, student {$studentId}, payment {$dataId}");
                                        break;
                                    }
                                } else {
                                    $overbookedGroup = true;
                                    Log::warning("Overbooking detectado por concurrencia SQL: session {$sid}, student {$studentId}, payment {$dataId}");
                                    break;
                                }
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
                    }
                } else {
                    Log::info("Pago {$dataId} ya estaba registrado para estudiante {$studentId}. Evitando duplicidad cruzada.");
                }
            }

            DB::commit();
            \Illuminate\Support\Facades\Cache::forget($lockKey);

            // =========================================================================
            // CAPA DE AISLAMIENTO: ACCIONES POST-PERSISTENCIA (Red / Mails / Colas)
            // =========================================================================

            // --- CASO B: OVERBOOKING DETECTADO DENTRO DE LA TRANSACCIÓN SQL ---
            if ($overbookedGroup) {
                try {
                    $studioToken = $studio?->mp_access_token;
                    if ($studioToken) {
                        $this->setToken($studioToken);
                    } else {
                        $this->setToken(config('services.mercadopago.token'));
                    }

                    $refundClient = new PaymentRefundClient();
                    $refund = $refundClient->refundTotal((int) $dataId);
                    Log::info("Overbooking SQL resuelto: reembolso emitido para payment {$dataId}, status {$refund->status}");
                } catch (\Exception $e) {
                    Log::error("Error crítico de red: No se pudo emitir reembolso MP en BD para payment {$dataId}: " . $e->getMessage());
                }

                foreach ($createdPayments as $item) {
                    try {
                        if ($item['student']->user && $item['firstSession']) {
                            $studioForMail = $studio ?? $item['firstSession']->workshop->studio;
                            Mail::to($item['student']->user->email)
                                ->queue(new \App\Mail\ClassRefundedMail(
                                    $item['firstSession'],
                                    $item['student'],
                                    $studioForMail,
                                    $item['payment']->amount
                                ));
                            Log::info("📧 [Mail encolado] ClassRefundedMail enviado a {$item['student']->user->email} por overbooking en SQL.");
                        } else {
                            Log::warning("📧 [Mail omitido] No se encontró el user para el alumno ID {$item['student']->id} en overbooking SQL.");
                        }
                    } catch (\Exception $e) {
                        Log::error('Error de red enviando ClassRefundedMail por overbooking SQL: ' . $e->getMessage());
                    }
                }

                return;
            }

            // --- CASO C: PAGO EXITOSO SIN SOBRECUPO ---

            try {
                $affectedSessionIds = collect($selectionsPagadas)->pluck('session_id')->unique()->toArray();
                app(EnrollmentService::class)->notifyCapacityChange($affectedSessionIds, 0, 'payment');
            } catch (\Exception $e) {
                Log::error("Error secundario al notificar cambio de capacidad en pago {$dataId}: " . $e->getMessage());
            }

            // 🚀 Despacho garantizado de correos y notificaciones
            foreach ($createdPayments as $item) {
                try {
                    if ($item['student']->user && $item['payment']->status === 'approved') {

                        $studentName = $item['student']->name;

                        // Garantizar que tenemos el estudio con la relación user cargada
                        $studioForMail = $studio?->relationLoaded('user')
                            ? $studio
                            : optional($item['firstSession']->workshop ?? null)->studio;
                        if ($studioForMail && !$studioForMail->relationLoaded('user')) {
                            $studioForMail->load('user');
                        }

                        // 1️⃣ AL ALUMNO [Notificación In-App]
                        $item['student']->user->notify(
                            new \App\Notifications\StudentPaymentApprovedNotification($item['payment'])
                        );
                        Log::info("🔔 [Notificación In-App encolada] Campana actualizada para {$item['student']->user->email}.");

                        // 2️⃣ AL ALUMNO [Correo Electrónico]
                        if ($studioForMail) {
                            Mail::to($item['student']->user->email)
                                ->queue(new \App\Mail\StudentPaymentReceiptMail(
                                    $studioForMail,
                                    $item['payment'],
                                    $studentName
                                ));
                            Log::info("📧 [Mail Alumno encolado] StudentPaymentReceiptMail enviado a {$item['student']->user->email}.");
                        }

                        // 3️⃣ AL ESTUDIO [Correo Electrónico]
                        if ($studioForMail && $studioForMail->user) {
                            Mail::to($studioForMail->user->email)
                                ->queue(new \App\Mail\StudioPaymentNotificationMail(
                                    $studioForMail,
                                    $item['payment'],
                                    $studentName
                                ));
                            Log::info("📧 [Mail Estudio encolado] StudioPaymentNotificationMail enviado a {$studioForMail->user->email}.");
                        }

                    }
                } catch (\Exception $e) {
                    Log::error("Error secundario enviando correos/notificaciones para pago {$dataId}: " . $e->getMessage());
                }
            }

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Illuminate\Support\Facades\Cache::forget($lockKey);
            Log::error("Error crítico transaccional asignando pago {$dataId} en BD: " . $e->getMessage() . " - Línea: " . $e->getLine());
            throw $e;
        }
    }

    // =========================================================================
    // LÓGICA AISLADA: PAGO DE FACTURA DE PLATAFORMA (FLOOR-CAPPED)
    // =========================================================================
    private function processPlatformInvoicePayment($dataId, $mpPayment, array $meta): void
    {
        $lockKey = "platform_invoice_payment_lock_{$dataId}";

        if (!\Illuminate\Support\Facades\Cache::add($lockKey, true, 60)) {
            Log::info("Idempotencia Concurrente: El pago de factura {$dataId} ya está siendo procesado. Ignorando clon.");
            return;
        }

        try {
            $invoiceId = $meta['invoice_id'] ?? null;
            $studioId  = $meta['studio_id'] ?? null;

            if (!$invoiceId || !$studioId) {
                Log::warning("Pago de factura {$dataId} sin invoice_id o studio_id en metadata.");
                return;
            }

            $invoice = \App\Models\StudioInvoice::where('id', $invoiceId)
                ->where('studio_id', $studioId)
                ->first();

            if (!$invoice) {
                Log::warning("Factura #{$invoiceId} no encontrada para pago {$dataId}.");
                return;
            }

            if ($invoice->isPaid()) {
                Log::info("Factura #{$invoiceId} ya estaba pagada. Ignorando pago duplicado {$dataId}.");
                return;
            }

            $status = $mpPayment->status ?? 'unknown';

            if ($status !== 'approved') {
                Log::info("Pago de factura {$dataId} ignorado. Estado: {$status}.");
                return;
            }

            $studio = \App\Models\Studio::find($studioId);
            if (!$studio) {
                Log::warning("Studio #{$studioId} no encontrado para pago de factura {$dataId}.");
                return;
            }

            // ═══════════════════════════════════════════════════════
            // TRANSACCIÓN ATÓMICA: Marcar factura + reducir ciclo founder
            // ═══════════════════════════════════════════════════════
            DB::transaction(function () use ($invoice, $studio) {
                // 1. Marcar factura como pagada
                $invoice->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);

                // 2. Reducción de ciclo Founder si aplica
                if ($studio->is_founder && $studio->founder_cycles_remaining > 0) {
                    $studio->decrement('founder_cycles_remaining');

                    // Refrescar para obtener el valor actualizado
                    $studio->refresh();

                    if ($studio->founder_cycles_remaining <= 0) {
                        $studio->update(['is_founder' => false]);
                        Log::info("👑 Beneficio Founder agotado para Studio #{$studio->id}. Ciclos consumidos.");
                    }

                    Log::info("👑 Ciclo Founder reducido: Studio #{$studio->id}, restantes: {$studio->founder_cycles_remaining}");
                }
            });

            // 3. Correos de notificación (fuera de la transacción, en cola)
            try {
                // A) Recibo para el dueño del estudio
                if ($studio->user && $studio->user->email) {
                    Mail::to($studio->user->email)
                        ->queue(new \App\Mail\PlatformInvoicePaidMail($studio, $invoice->fresh()));
                    Log::info("📧 Recibo de pago de factura encolado para Studio #{$studio->id}.");
                }

                // B) Alerta interna para la plataforma
                $adminEmail = config('mail.support_email', env('ADMIN_EMAIL', 'contacto@estadoprisma.com'));
                if ($adminEmail) {
                    Mail::to($adminEmail)
                        ->queue(new \App\Mail\AdminInvoicePaidAlertMail($studio, $invoice->fresh()));
                    Log::info("📧 Alerta interna de cobro de comisión encolada para {$adminEmail}.");
                }
                
            } catch (\Exception $e) {
                Log::error("Error encolando recibos/alertas de factura: " . $e->getMessage());
            }

            Log::info("✅ Factura #{$invoice->id} marcada como pagada. Studio #{$studio->id} liberado del bloqueo.");

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Cache::forget($lockKey);
            Log::error("Error procesando pago de factura {$dataId}: " . $e->getMessage());
            throw $e;
        }
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

    /**
     * Emite un reembolso total sobre un pago de Mercado Pago.
     */
    public function refundPayment(string $paymentId): object
    {
        $this->setToken(config('services.mercadopago.token'));

        $refundClient = new PaymentRefundClient();

        try {
            $refund = $refundClient->refundTotal((int) $paymentId);
            \Illuminate\Support\Facades\Log::info("Reembolso emitido para payment {$paymentId}. Estado: {$refund->status}");
            return $refund;
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            \Illuminate\Support\Facades\Log::error("Error al reembolsar payment {$paymentId}: " . $e->getMessage());
            throw new \Exception('No se pudo emitir el reembolso en este momento. Intenta más tarde.');
        }
    }

    // =========================================================================
    // GATEWAY DE PAGO: QR ESTÁTICO, QR DINÁMICO Y LINK POR CORREO
    // =========================================================================

    /**
     * Setup único: crea Store + POS en MercadoPago para el QR estático del estudio.
     * Se llama automáticamente al vincular MP vía OAuth, o manualmente desde AccountController.
     *
     * Implementa patrón find-or-create: primero busca si la tienda/POS ya existen
     * (usando los endpoints correctos de la API de In-Store, SIN el prefijo /v1),
     * y solo crea si no los encuentra. Extrae el QR de qr.image en la respuesta del POS.
     */
    public function setupStaticQR(Studio $studio): void
    {
        if (empty($studio->mp_access_token)) {
            throw new \Exception('El estudio no tiene una cuenta de MercadoPago vinculada.');
        }

        if (empty($studio->mp_user_id)) {
            throw new \Exception('No se pudo identificar el ID de usuario de MercadoPago. Reintenta la vinculación OAuth.');
        }

        $this->setToken($studio->mp_access_token);
        $apiBaseUrl = config('services.mercadopago.api_url', 'https://api.mercadopago.com');
        $mpUserId = $studio->mp_user_id;
        // IMPORTANTE: external_id debe ser estrictamente alfanumérico (sin guiones, sin símbolos)
        $externalStoreId = "STUDIO{$studio->id}";
        $posExternalId = $studio->mp_external_pos_id ?: "POSSTUDIO{$studio->id}";

        try {
            // ═══════════════════════════════════════════════════════════
            // 1. FIND OR CREATE STORE
            //    Endpoint correcto: POST /users/{user_id}/stores (sin /v1)
            // ═══════════════════════════════════════════════════════════
            $storeId = $studio->mp_store_id;

            if (empty($storeId)) {
                // Intentar encontrar tienda ya existente por external_id
                $storeId = $this->findExistingStore($apiBaseUrl, $mpUserId, $externalStoreId, $studio->mp_access_token);

                if (!$storeId) {
                    // Construir location requerido por la API
                    $location = [
                        'street_name'   => $studio->address ?: 'Sin dirección registrada',
                        'street_number' => 'S/N',
                        'city_name'     => $studio->city ?: 'Santiago',
                        'state_name'    => $studio->region ?: 'Región Metropolitana',
                        'latitude'      => (float) ($studio->latitude ?: -33.448),
                        'longitude'     => (float) ($studio->longitude ?: -70.669),
                    ];

                    // Crear nueva tienda
                    $storeResponse = Http::withToken($studio->mp_access_token)
                        ->post("{$apiBaseUrl}/users/{$mpUserId}/stores", [
                            'name'        => $studio->name,
                            'external_id' => $externalStoreId,
                            'location'    => $location,
                        ]);

                    if (!$storeResponse->successful()) {
                        Log::error('MP Store creation failed', [
                            'studio_id' => $studio->id,
                            'status'    => $storeResponse->status(),
                            'response'  => $storeResponse->json(),
                        ]);
                        throw new \Exception('No se pudo crear la tienda en MercadoPago. Verifica que tu cuenta tenga los permisos necesarios (scope de In-Store/QR).');
                    }

                    $storeData = $storeResponse->json();
                    $storeId = (string) ($storeData['id'] ?? throw new \Exception('No se recibió el ID de la tienda desde MercadoPago.'));

                    // Verificar que el external_id devuelto coincida con el enviado
                    $returnedExternalId = $storeData['external_id'] ?? null;
                    if ($returnedExternalId && $returnedExternalId !== $externalStoreId) {
                        Log::warning('MP Store external_id divergente', [
                            'enviado'  => $externalStoreId,
                            'devuelto' => $returnedExternalId,
                        ]);
                        $externalStoreId = $returnedExternalId;
                    }

                    Log::info("Store creada para estudio {$studio->id}", [
                        'store_id'     => $storeId,
                        'external_id'  => $externalStoreId,
                    ]);
                } else {
                    Log::info("Store existente encontrada para estudio {$studio->id}", ['store_id' => $storeId]);
                }

                $studio->update(['mp_store_id' => $storeId]);
            }

            // ═══════════════════════════════════════════════════════════
            // 2. FIND OR CREATE POS (y extraer QR)
            //    Endpoint correcto: POST /pos (sin /v1 ni ruta anidada bajo store)
            // ═══════════════════════════════════════════════════════════
            $qrImageUrl = null;

            // Intentar encontrar POS existente
            $existingPos = $this->findExistingPos($apiBaseUrl, $storeId, $posExternalId, $studio->mp_access_token);

            if ($existingPos) {
                $posExternalId = $existingPos['external_id'] ?? $posExternalId;
                // El QR está en qr.image (URL a PNG), también hay qr.template_document y qr.template_image
                $qrImageUrl = $existingPos['qr']['image'] ?? null;
                Log::info("POS existente encontrado para estudio {$studio->id}", [
                    'pos_external_id' => $posExternalId,
                    'has_qr'          => !empty($qrImageUrl),
                ]);
            }

            // Si no se encontró POS o no tiene QR, crear uno nuevo
            if (empty($qrImageUrl)) {
                $posResponse = Http::withToken($studio->mp_access_token)
                    ->post("{$apiBaseUrl}/pos", [
                        'name'               => 'Caja Principal',
                        'fixed_amount'       => false,
                        'store_id'           => (int) $storeId,
                        'external_store_id'  => $externalStoreId,
                        'external_id'        => $posExternalId,
                        // Sin 'category' → queda como categoría genérica (evita error pos_unknown_mcc)
                    ]);

                if (!$posResponse->successful()) {
                    Log::error('MP POS creation failed', [
                        'studio_id' => $studio->id,
                        'store_id'  => $storeId,
                        'status'    => $posResponse->status(),
                        'response'  => $posResponse->json(),
                    ]);
                    throw new \Exception('No se pudo crear el punto de venta en MercadoPago. Revisa los permisos de tu cuenta.');
                }

                $posData = $posResponse->json();
                $posExternalId = $posData['external_id'] ?? $posExternalId;
                $qrImageUrl = $posData['qr']['image'] ?? null;

                Log::info("POS creado para estudio {$studio->id}", [
                    'pos_external_id' => $posExternalId,
                    'has_qr'          => !empty($qrImageUrl),
                ]);
            }

            if (empty($qrImageUrl)) {
                throw new \Exception('No se pudo obtener la imagen del QR estático desde MercadoPago. La respuesta del POS no incluyó qr.image.');
            }

            // ═══════════════════════════════════════════════════════════
            // 3. PERSISTIR QR EN EL ESTUDIO
            // ═══════════════════════════════════════════════════════════
            $studio->update([
                'mp_external_pos_id' => $posExternalId,
                'mp_pos_qr_url'      => $qrImageUrl,
            ]);

            Log::info("Static QR setup completado para estudio {$studio->id}", [
                'store_id'   => $storeId,
                'pos_ext_id' => $posExternalId,
                'qr_url'     => $qrImageUrl,
            ]);

        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No se pudo')) {
                throw $e;
            }
            Log::error('Error inesperado en setupStaticQR: ' . $e->getMessage(), [
                'studio_id' => $studio->id,
                'trace'     => $e->getTraceAsString(),
            ]);
            throw new \Exception('Error al configurar el QR estático: ' . $e->getMessage());
        }
    }

    /**
     * Busca una tienda existente en MercadoPago por su external_id.
     * Retorna el store_id (string) si existe, o null si no.
     */
    private function findExistingStore(string $apiBaseUrl, string $mpUserId, string $externalStoreId, string $accessToken): ?string
    {
        try {
            // Endpoint correcto: GET /users/{user_id}/stores/search?external_id=...
            $response = Http::withToken($accessToken)
                ->get("{$apiBaseUrl}/users/{$mpUserId}/stores/search", [
                    'external_id' => $externalStoreId,
                ]);

            if (!$response->successful()) {
                Log::warning('MP find store request failed', [
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
                return null;
            }

            $data = $response->json();

            // La respuesta tiene formato {paging: {...}, results: [...]}
            $stores = $data['results'] ?? $data;
            if (!is_array($stores)) {
                return null;
            }

            foreach ($stores as $store) {
                if (($store['external_id'] ?? '') === $externalStoreId) {
                    return (string) $store['id'];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Error buscando store existente: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca un POS existente en MercadoPago por su external_id.
     * Retorna el array completo del POS si existe, o null si no.
     */
    private function findExistingPos(string $apiBaseUrl, string $storeId, string $externalPosId, string $accessToken): ?array
    {
        try {
            // GET /pos?external_id=... (la API de POS no usa sufijo /search, solo query params)
            $response = Http::withToken($accessToken)
                ->get("{$apiBaseUrl}/pos", [
                    'external_id' => $externalPosId,
                ]);

            if (!$response->successful()) {
                Log::warning('MP find POS request failed', [
                    'status'   => $response->status(),
                    'response' => $response->json(),
                ]);
                return null;
            }

            $data = $response->json();

            // La API devuelve {paging: {...}, results: [...]}
            $posList = $data['results'] ?? $data;
            if (!is_array($posList)) {
                return null;
            }

            foreach ($posList as $pos) {
                if (($pos['external_id'] ?? '') === $externalPosId) {
                    return $pos;
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Error buscando POS existente: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera una Orden de pago presencial (QR estático) para las sesiones seleccionadas.
     * La alumna escanea el QR fijo del estudio y ve el monto pre-cargado en su app de MP.
     */
    public function generateStaticQROrder(Studio $studio, array $sessionIds, Student $student): array
    {
        if (empty($studio->mp_access_token)) {
            throw new \Exception('Este estudio no tiene una cuenta de MercadoPago vinculada.');
        }

        if (empty($studio->mp_external_pos_id)) {
            throw new \Exception('El QR estático aún no está configurado. Usa "QR Normal" o contacta a soporte.');
        }

        if (empty($sessionIds)) {
            throw new \Exception('Debes seleccionar al menos una clase.');
        }

        // Calcular precio con PricingService
        $result = $this->pricingService->calculateCart($studio->id, $sessionIds, $student->id);

        if ($result['total'] <= 0) {
            throw new \Exception('El total a pagar debe ser mayor a 0.');
        }

        $this->setToken($studio->mp_access_token);

        $sessionIdsParam = array_map('intval', $sessionIds);

        // external_reference: la API de Orders requiere ≤64 chars alfanumérico.
        // Guardamos la metadata completa en Cache y usamos un ID corto como referencia.
        $shortRef = 'QR' . $studio->id . 'S' . $student->id . 'T' . time();
        $fullMeta = [
            'type'        => 'student_payment',
            'studio_id'   => $studio->id,
            'student_id'  => $student->id,
            'session_ids' => $sessionIdsParam,
        ];
        \Illuminate\Support\Facades\Cache::put("mp_order_meta:{$shortRef}", $fullMeta, now()->addDays(7));

        $items = array_map(function ($b) {
            return [
                'title'        => $b['name'],
                'unit_price'   => (string) $b['subtotal'],
                'quantity'     => 1,
                'unit_measure' => 'unit',
            ];
        }, $result['breakdown']);

        $webhookDomain = config('services.mercadopago.webhook_domain') ?: rtrim(config('app.url'), '/');

        $request = [
            'type'               => 'qr',
            'total_amount'       => (string) $result['total'],
            'external_reference' => $shortRef,
            'notification_url'   => rtrim($webhookDomain, '/') . '/api/webhooks/mercadopago',
            'config' => [
                'qr' => [
                    'external_pos_id' => $studio->mp_external_pos_id,
                    'mode'            => 'static',
                ],
            ],
            'transactions' => [
                'payments' => [
                    [
                        'amount' => (string) $result['total'],
                    ],
                ],
            ],
            'items' => $items,
        ];

        try {
            $client = new OrderClient();
            $order = $client->create($request);

            Log::info("Static QR Order creada para estudio {$studio->id}, orden {$order->id}", [
                'student_id' => $student->id,
                'total'      => $result['total'],
            ]);

            return [
                'order_id'  => $order->id,
                'total'     => $result['total'],
                'qr_url'    => $studio->mp_pos_qr_url,
                'breakdown' => $result['breakdown'],
            ];
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            $apiResponse = $e->getApiResponse();
            $responseBody = $apiResponse ? $apiResponse->getContent() : null;
            Log::error('MP Order creation failed: ' . $e->getMessage(), [
                'studio_id'     => $studio->id,
                'api_status'    => $e->getStatusCode(),
                'api_response'  => $responseBody,
                'request'       => $request,
            ]);
            throw new \Exception('Error al generar la orden de pago: ' . $e->getMessage());
        }
    }

    /**
     * Genera una preferencia de Checkout Pro simplificada para un solo estudiante.
     * Usado por QR Normal y Link por Correo.
     */
    public function generateGatewayPreference(Studio $studio, array $sessionIds, Student $student): array
    {
        if (empty($studio->mp_access_token)) {
            throw new \Exception('Este estudio no tiene una cuenta de MercadoPago vinculada.');
        }

        if (empty($sessionIds)) {
            throw new \Exception('Debes seleccionar al menos una clase.');
        }

        // Calcular precio con PricingService
        $result = $this->pricingService->calculateCart($studio->id, $sessionIds, $student->id);

        if ($result['total'] <= 0) {
            throw new \Exception('El total a pagar debe ser mayor a 0.');
        }

        $this->setToken($studio->mp_access_token);

        $sessionIdsParam = array_map('intval', $sessionIds);
        $baseUrl = rtrim(config('app.url'), '/');
        $webhookDomain = config('services.mercadopago.webhook_domain') ?: rtrim(config('app.url'), '/');

        $items = array_map(function ($b) use ($studio) {
            return [
                'title'       => $b['name'],
                'quantity'    => 1,
                'unit_price'  => (float) $b['subtotal'],
                'currency_id' => $studio->currency_code,
            ];
        }, $result['breakdown']);

        $selections = array_map(function ($sid) use ($student) {
            return [
                'session_id' => (int) $sid,
                'student_id' => $student->id,
            ];
        }, $sessionIdsParam);

        $request = [
            'items'               => $items,
            'statement_descriptor' => strtoupper(substr(preg_replace('/[^a-zA-Z0-9 ]/', '', $studio->name), 0, 20)),
            'external_reference'   => json_encode([
                'type'       => 'student_payment',
                'user_id'    => $student->user_id,
                'selections' => $selections,
                'studio_id'  => $studio->id,
            ]),
            'back_urls' => [
                'success' => $baseUrl . '/pagos/exito',
                'failure' => $baseUrl . '/pagos/error',
                'pending' => $baseUrl . '/pagos/pendiente',
            ],
            'auto_return'      => 'approved',
            'notification_url' => rtrim($webhookDomain, '/') . '/api/webhooks/mercadopago',
        ];

        // NOTA: El 100% va al token del estudio. La plataforma factura su comisión aparte.

        try {
            $client = new PreferenceClient();
            $preference = $client->create($request);

            if (!$preference->init_point) {
                throw new \Exception('Error al generar el link de pago con MercadoPago.');
            }

            Log::info("Gateway Preference creada para estudio {$studio->id}, estudiante {$student->id}", [
                'preference_id' => $preference->id,
                'total'         => $result['total'],
            ]);

            return [
                'init_point'    => $preference->init_point,
                'preference_id' => $preference->id,
                'total'         => $result['total'],
                'breakdown'     => $result['breakdown'],
            ];
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            Log::error('MP Preference creation failed: ' . $e->getMessage(), [
                'studio_id' => $studio->id,
            ]);
            throw new \Exception('Error al generar el link de pago: ' . $e->getMessage());
        }
    }

    /**
     * Genera una preferencia de pago y envía el link por correo al estudiante.
     */
    public function sendPaymentEmail(Studio $studio, array $sessionIds, Student $student): array
    {
        if (empty($student->email)) {
            throw new \Exception('El alumno no tiene correo electrónico registrado. Edita su perfil para agregar uno.');
        }

        // Generar la preferencia de pago
        $preferenceData = $this->generateGatewayPreference($studio, $sessionIds, $student);

        // Determinar tipo de pago
        $classCount = count($sessionIds);
        $breakdown = $preferenceData['breakdown'];
        $hasDiscount = !empty(array_filter($breakdown, fn($b) => $b['is_discount'] ?? false));
        $hasPromo = $hasDiscount; // descuentos vienen de promociones

        if ($hasPromo) {
            $paymentType = 'promocion';
        } elseif ($classCount > 1) {
            $paymentType = 'pack';
        } else {
            $paymentType = 'single';
        }

        // Enviar correo con el link de pago
        try {
            Mail::to($student->email)->queue(
                new \App\Mail\StudentPaymentLinkMail(
                    $studio,
                    $student,
                    $preferenceData['init_point'],
                    $preferenceData['total'],
                    $paymentType,
                    $classCount,
                    $breakdown,
                )
            );

            Log::info("Payment link email queued para {$student->email}", [
                'studio_id'    => $studio->id,
                'student_id'   => $student->id,
                'total'        => $preferenceData['total'],
                'payment_type' => $paymentType,
                'class_count'  => $classCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Error enviando correo de link de pago: ' . $e->getMessage(), [
                'student_email' => $student->email,
            ]);
            throw new \Exception('El link de pago se generó, pero hubo un error al enviar el correo. Comparte el link manualmente.');
        }

        return $preferenceData;
    }
}