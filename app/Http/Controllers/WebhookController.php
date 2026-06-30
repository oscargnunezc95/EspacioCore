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

        // ID único de la notificación (a nivel raíz). MP lo genera para cada
        // envío; sirve como llave de deduplicación contra webhooks clonados.
        $webhookId = $request->input('id');

        if (!$dataId) {
            return response()->json(['status' => 'ok'], 200);
        }

        // 🚨 GUARDIA DE DEDUPLICACIÓN GLOBAL: si este webhook exacto ya fue
        //    procesado, respondemos OK inmediatamente sin volver a ejecutar.
        $dedupeKey = "mp_webhook_dedup_{$webhookId}";
        if (!\Illuminate\Support\Facades\Cache::add($dedupeKey, true, 3600)) {
            Log::info("Webhook duplicado ignorado: {$webhookId} (type={$type})");
            return response()->json(['status' => 'ok'], 200);
        }

        // 🚨 BLINDAJE DE TÓPICOS: Descartar silenciosamente las merchant_orders
        if ($type === 'merchant_order') {
            Log::info("Webhook ignorado: Tópico {$type} no requiere procesamiento.");
            return response()->json(['status' => 'ok'], 200);
        }

        try {
            switch ($type) {
                // ---------------------------------------------------------
                // FLUJO 1: SUSCRIPCIONES SAAS (Estudios -> EstadoPrisma)
                // ---------------------------------------------------------
                case 'subscription_preapproval':
                case 'preapproval':
                    // ⚠️ NO castear a (int): los preapproval_id de MP ahora son UUIDs
                    $mpService->processSaaSSubscription($dataId);
                    break;

                // ---------------------------------------------------------
                // FLUJO 2: PAGOS (Director de Orquesta para Alumnos/Profesores)
                // ---------------------------------------------------------
                case 'payment':
                case 'subscription_authorized_payment':
                    $mpService->handlePaymentWebhook($dataId);
                    break;
            }
        } catch (\Exception $e) {
            Log::error("Error Crítico procesando Webhook [{$type}]: " . $e->getMessage() . " Línea: " . $e->getLine());
            return response()->json(['status' => 'ok'], 200);
        }

        // Siempre devolver 200 OK a Mercado Pago
        return response()->json(['status' => 'ok'], 200);
    }
}