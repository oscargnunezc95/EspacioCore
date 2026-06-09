<?php

namespace App\Services;

use App\Models\Studio;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\PricingService;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;
use MercadoPago\Client\Payment\PaymentClient;
use MercadoPago\Client\PreApproval\PreApprovalClient;

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

    public function processStudentPayment($dataId, $mpUserId = null)
    {
        $this->setToken(config('services.mercadopago.token'));

        $client = new PaymentClient();
        try {
            $mpPayment = $client->get((int) $dataId);
        } catch (\MercadoPago\Exceptions\MPApiException $e) {
            // ERROR: El pago no existe en la API o el ID es de prueba
            Log::error("MercadoPago API Error: No se pudo obtener el pago {$dataId}. Detalles: " . $e->getMessage());
            return; // Salimos limpiamente sin romper el webhook
        }

        if (!$mpPayment || $mpPayment->status !== 'approved') {
            Log::info("Pago {$dataId} ignorado o no aprobado.");
            return;
        }

        if (!$mpPayment || $mpPayment->status !== 'approved') {
            Log::info("Pago {$dataId} ignorado o no aprobado. Estado: " . ($mpPayment->status ?? 'null'));
            return;
        }

        $meta = json_decode($mpPayment->external_reference, true);
        $selectionsPagadas = $meta['selections'] ?? [];
        $userId = $meta['user_id'] ?? null;
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
        
        // Prorrateamos tu comisión por cada selección para que el Ledger cuadre a 0
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

                $payment = Payment::create([
                    'student_id'     => $studentId,
                    'studio_id'      => $firstSession->studio_id ?? $studioId,
                    'workshop_id'    => $firstSession->workshop_id,
                    'payment_type'   => count($sessionIds) == 1 ? 'single' : 'pack',
                    'payment_method' => 'mercadopago',
                    'amount'         => $paymentAmount,
                    'platform_fee'   => $paymentFee, // <-- REGISTRO DE TU INGRESO
                    'mp_payment_id'  => $dataId,
                    'status'         => 'approved',
                ]);

                $pivotData = [];
                foreach ($sessionIds as $sid) {
                    $pivotData[$sid] = ['student_id' => $studentId];
                }
                $payment->classSessions()->attach($pivotData);

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
            }

            DB::commit();

            // Lógica de llenado de cupos y notificación a alumnos pendientes (lista de espera visual)
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
                'type'       => 'teacher_payment',
            ]),
            'back_urls' => [
                'success' => $this->absoluteUrl($data['success_url']),
                'failure' => $this->absoluteUrl($data['failure_url']),
                'pending' => $this->absoluteUrl($data['failure_url']),
            ],
            'auto_return' => 'approved',
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
     * Cancela una suscripción activa (Preapproval) en Mercado Pago.
     */
    public function cancelPreapproval(string $preapprovalId): void
    {
        // Usamos el token global de tu plataforma (EstadoPrisma)
        $this->setToken(config('services.mercadopago.token'));
        
        $client = new \MercadoPago\Client\PreApproval\PreApprovalClient();
        
        // Actualizamos el estado a 'cancelled'
        $client->update($preapprovalId, [
            "status" => "cancelled"
        ]);
        
        \Illuminate\Support\Facades\Log::info("Suscripción {$preapprovalId} cancelada exitosamente vía API.");
    }
}