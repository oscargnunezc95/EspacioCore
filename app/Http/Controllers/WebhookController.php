<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SubscriptionReceiptMail;
use App\Mail\NewSubscriptionAlertMail;

class WebhookController extends Controller
{
    /**
     * Recibe y procesa TODAS las notificaciones de Mercado Pago.
     */
    public function MercadoPago(Request $request, MercadoPagoService $mpService)
    {
        Log::info('Webhook recibido de MP:', $request->all());

        // BLINDADO: MP manda Webhooks (type/action/data.id) o IPNs (topic/id)
        $type = $request->input('type') ?? $request->input('topic') ?? $request->input('action');
        $dataId = $request->input('data.id') ?? $request->input('id');
        
        // El user_id de Mercado Pago nos sirve para saber a qué estudio le pagaron (Marketplace)
        $mpUserId = $request->input('user_id'); 

        if (!$dataId) {
            return response()->json(['status' => 'ignored', 'message' => 'No data ID'], 200);
        }

        try {
            switch ($type) {
                // ---------------------------------------------------------
                // FLUJO 1: SUSCRIPCIONES SAAS (Estudios -> EstadoPrisma)
                // ---------------------------------------------------------
                case 'subscription_preapproval':
                case 'subscription_authorized': // A veces MP usa esta variante
                    $this->processSaaSSubscription($dataId, $mpService);
                    break;

                // ---------------------------------------------------------
                // FLUJO 2: PAGOS DE ALUMNAS (Alumnas -> Estudios)
                // ---------------------------------------------------------
                case 'payment':
                case 'payment.created':
                case 'payment.updated':
                case 'merchant_order':
                    // Delegamos la inscripción al Servicio
                    $mpService->processStudentPayment($dataId, $mpUserId);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Error Crítico procesando Webhook [{$type}]: " . $e->getMessage() . " Línea: " . $e->getLine());
            return response()->json(['error' => 'Internal Server Error'], 500); 
        }

        // Siempre devolver 200 OK a Mercado Pago
        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Lógica encapsulada para mantener el controlador limpio
     */
    private function processSaaSSubscription($dataId, MercadoPagoService $mpService)
    {
        $subscription = $mpService->getSubscriptionDetails($dataId);
                
        $studioId = $subscription['external_reference'] ?? null;
        $status = $subscription['status'] ?? null;

        if ($studioId && $studio = Studio::find($studioId)) {
            if ($status === 'authorized') {
                $studio->update([
                    'mp_preapproval_id' => $dataId,
                    'subscription_status' => 'pro'
                ]);

                Mail::to($studio->user->email)->send(new SubscriptionReceiptMail($studio, 'Pro', 45000));
                Mail::to('oscar@estadoprisma.test')->send(new NewSubscriptionAlertMail($studio, 'Pro'));

            } elseif (in_array($status, ['paused', 'cancelled'])) {
                $studio->update(['subscription_status' => 'free']);
            }
        }
    }
}