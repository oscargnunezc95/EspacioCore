<?php

namespace App\Services;

use App\Models\Studio;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\PricingService;

class MercadoPagoService
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    /**
     * -----------------------------------------------------------------
     * FASE 1: CHECKOUT (Generar link de pago)
     * -----------------------------------------------------------------
     */
    public function createPreference(int $studioId, array $sessionIds, $user)
    {
        $studio = Studio::findOrFail($studioId);

        if (!$studio->mp_access_token) {
            throw new \Exception("El estudio aún no está habilitado para recibir pagos online.");
        }

        $cartData = $this->pricingService->calculateCart($studioId, $sessionIds);
        $totalAmount = $cartData['total'];

        if ($totalAmount <= 0) {
            throw new \Exception("El monto a pagar no es válido.");
        }

        $domain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';
        $protocol = request()->secure() ? 'https://' : 'http://';
        $baseUrl = $protocol . $studio->subdomain . '.' . $domain;

        $payload = [
            'items' => [
                [
                    'title'       => 'Reserva de Clases - ' . $studio->name,
                    'description' => count($sessionIds) . ' clase(s) seleccionada(s)',
                    'quantity'    => 1,
                    'unit_price'  => (float) $totalAmount,
                    'currency_id' => 'CLP',
                ]
            ],
            // 👇 ELIMINAMOS ESTO POR COMPLETO 👇
            // 'payer' => [
            //     'email' => 'TESTUSER1313865840731392797@testuser.com',
            //     'name'  => 'APRO',
            // ],
            'back_urls' => [
                'success' => $baseUrl . '/pagos/exito',
                'pending' => $baseUrl . '/pagos/pendiente',
                'failure' => $baseUrl . '/pagos/error',
            ],
            'auto_return' => 'approved',
            // Empaquetamos la metadata crítica aquí para recuperarla en el Webhook
            'external_reference' => json_encode([
                'studio_id'   => $studioId, 
                'user_id'     => $user->id, 
                'session_ids' => $sessionIds
            ]),
            'notification_url' => 'https://quotable-draw-decipher.ngrok-free.dev/api/webhooks/mercadopago',
        ];

        $response = Http::withToken($studio->mp_access_token)
            ->post('https://api.mercadopago.com/checkout/preferences', $payload);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Error al crear preferencia MP: ', $response->json());
        throw new \Exception("Fallo de comunicación con Mercado Pago.");
    }

    /**
     * -----------------------------------------------------------------
     * FASE 2: WEBHOOK DE ALUMNAS (Procesar Pago Recibido)
     * -----------------------------------------------------------------
     */
    public function processStudentPayment($paymentId, $mpUserId)
    {
        $studio = Studio::where('mp_user_id', $mpUserId)->first();

        if (!$studio) {
            Log::error("Webhook MP: Estudio no encontrado para el mp_user_id {$mpUserId}");
            return;
        }

        $response = Http::withToken($studio->mp_access_token)
            ->get("https://api.mercadopago.com/v1/payments/{$paymentId}");

        if (!$response->successful() || $response->json()['status'] !== 'approved') {
            return; 
        }

        $paymentData = $response->json();
        $reference = json_decode($paymentData['external_reference'], true);

        if (!$reference || !isset($reference['user_id'], $reference['session_ids'])) {
            return;
        }

        // Transacción ACID: Si falla una inscripción, se revierte el pago entero
        DB::transaction(function () use ($paymentData, $studio, $reference) {
            
            // 1. Evitamos dobles cobros (Idempotencia)
            if (Payment::where('mp_payment_id', $paymentData['id'])->exists()) {
                return; 
            }

            $user = \App\Models\User::find($reference['user_id']);

            // 2. MAGIA ARQUITECTÓNICA: Asegurar que exista la ficha de Alumna.
            $student = Student::firstOrCreate(
                ['user_id' => $user->id, 'studio_id' => $studio->id],
                [
                    'first_name'  => $user->name,
                    'email'       => $user->email,
                    'national_id' => $user->national_id,
                    'country_id'  => $user->country_id,
                    'is_guest'    => 0,
                ]
            );

            // 3. Obtenemos el ID del Taller de la primera clase para cumplir con la BD
            $firstSession = \App\Models\ClassSession::find($reference['session_ids'][0]);
            if (!$firstSession) return;

            // 4. Creamos el Recibo Oficial
            $payment = Payment::create([
                'studio_id'      => $studio->id,
                'student_id'     => $student->id,
                'workshop_id'    => $firstSession->workshop_id,
                'amount'         => $paymentData['transaction_amount'],
                'payment_method' => 'mercadopago',
                'payment_type'   => count($reference['session_ids']) > 1 ? 'pack' : 'clase_suelta',
                'mp_payment_id'  => $paymentData['id'],
                'status'         => 'approved'
            ]);

            // 5. --- LÓGICA DE INSCRIPCIÓN EN TABLAS PIVOTE ---
            foreach ($reference['session_ids'] as $sessionId) {
                
                // A) Vinculamos el pago histórico a esta sesión
                $payment->classSessions()->syncWithoutDetaching([
                    $sessionId => ['student_id' => $student->id]
                ]);

                // B) MAGIA DE OPTIMIZACIÓN: Actualizamos el estado a 'paid'
                // Esto anula su deuda y la quita de la vista del carrito instantáneamente.
                $student->classSessions()->syncWithoutDetaching([
                    $sessionId => ['payment_status' => 'paid'] 
                ]);
            }

            Log::info("¡ÉXITO! Pago {$paymentData['id']} procesado e inscripciones confirmadas para la alumna {$student->id}");
        });
        // 👇 INYECTAR FLUJO DE COMUNICACIÓN AQUÍ 👇
        try {
            $payment = \App\Models\Payment::where('mp_payment_id', $paymentData['id'])->first();
            
            if ($payment && $payment->student) {
                $student = $payment->student;
                
                if ($student->email) {
                    \Illuminate\Support\Facades\Mail::to($student->email)->send(
                        new \App\Mail\StudentPaymentReceiptMail($studio, $payment, $student->first_name)
                    );
                }
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Webhook MP: Pago guardado, pero falló el envío del correo: ' . $e->getMessage());
        }
    }

    /**
     * -----------------------------------------------------------------
     * FASE 3: WEBHOOK SAAS (Tus propias Suscripciones)
     * -----------------------------------------------------------------
     */
    public function getSubscriptionDetails($preapprovalId)
    {
        // Reemplaza 'TU_ACCESS_TOKEN_MAESTRO' por el tuyo propio de .env
        $masterToken = env('MERCADOPAGO_ACCESS_TOKEN'); 

        $response = Http::withToken($masterToken)
            ->get("https://api.mercadopago.com/preapproval/{$preapprovalId}");

        if ($response->successful()) {
            return $response->json();
        }

        throw new \Exception("No se pudo obtener la suscripción {$preapprovalId} de MP.");
    }
}