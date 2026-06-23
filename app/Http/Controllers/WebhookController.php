<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    /**
     * Recibe y procesa TODAS las notificaciones de Mercado Pago.
     */
    public function mercadopago(Request $request, MercadoPagoService $mpService)
    {
        Log::info('Webhook recibido de MP:', $request->all());

        // MP manda Webhooks (type/action/data.id) o IPNs (topic/id)
        $type = $request->input('type') ?? $request->input('topic') ?? $request->input('action');
        $dataId = $request->input('data.id') ?? $request->input('id');
        
        if (!$dataId) {
            return response()->json(['status' => 'ignored', 'message' => 'No data ID'], 200);
        }

        // 🚨 BLINDAJE DE TÓPICOS: Descartar silenciosamente las merchant_orders
        if ($type === 'merchant_order') {
            Log::info("Webhook ignorado: Tópico {$type} no requiere procesamiento.");
            return response()->json(['status' => 'ignored'], 200);
        }

        try {
            switch ($type) {
                // ---------------------------------------------------------
                // FLUJO 1: SUSCRIPCIONES SAAS (Estudios -> EstadoPrisma)
                // ---------------------------------------------------------
                case 'subscription_preapproval':
                case 'subscription_authorized':
                    $mpService->processSaaSSubscription((int) $dataId);
                    break;

                // ---------------------------------------------------------
                // FLUJO 2: PAGOS (Director de Orquesta para Alumnos/Profesores)
                // ---------------------------------------------------------
                case 'payment':
                case 'payment.created':
                case 'payment.updated':
                    // Enviamos el ID al Dispatcher del servicio
                    $mpService->handlePaymentWebhook($dataId);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Error Crítico procesando Webhook [{$type}]: " . $e->getMessage() . " Línea: " . $e->getLine());
            return response()->json(['error' => 'Internal Server Error'], 500); 
        }

        // Siempre devolver 200 OK a Mercado Pago
        return response()->json(['status' => 'success'], 200);
    }
}