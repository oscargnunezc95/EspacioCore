<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Models\StudioInvoice;
use App\Models\Studio;
use App\Mail\PlatformInvoicePaidMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class WebhookController extends Controller
{
    /**
     * Recibe y procesa las notificaciones de Mercado Pago con blindaje criptográfico v2
     * y registro exhaustivo de éxitos en el sistema de logs de Laravel.
     */
    public function mercadopago(Request $request, MercadoPagoService $mpService)
    {
        $type = $request->input('type') ?? $request->input('topic') ?? $request->input('action');
        $dataId = $request->input('data.id') ?? $request->input('id');
        $webhookId = $request->input('id');

        // 1. IDENTIFICACIÓN Y SALIDA TEMPRANA DE FORMATOS LEGACY (IPN / Merchant Orders)
        // Si el payload contiene 'topic' o 'resource', es una notificación IPN antigua.
        // No aplicamos HMAC v2 aquí para evitar falsos positivos de seguridad.
        if (!$dataId || $type === 'merchant_order' || $request->has('topic') || $request->has('resource')) {
            Log::info("ℹ️ [MP Webhook] Notificación IPN/Legacy ignorada pacíficamente. ID: {$dataId} (type/topic={$type})");
            return response()->json(['status' => 'legacy_ignored'], 200);
        }

        // 2. BLINDAJE CRIPTOGRÁFICO PARA WEBHOOKS V2
        $signatureCheck = $this->verifyMercadoPagoSignature($request);
        
        if ($signatureCheck === 'missing_headers') {
            Log::info("ℹ️ [MP Webhook] Petición sin cabeceras de seguridad recibida para ID: {$dataId}. Aceptada en frontera sin procesar.");
            return response()->json(['status' => 'no_headers_ignored'], 200);
        }

        if ($signatureCheck === 'invalid') {
            Log::warning("🚨 [MP Webhook] Alerta de Seguridad: Firma criptográfica rechazada.", [
                'ip' => $request->ip(),
                'payload' => $request->all()
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 3. IDEMPOTENCIA EN FRONTERA (Evitar webhooks clonados)
        // Capa 1: Dedup por ID de notificación (cada notificación única se procesa una sola vez)
        $dedupeKey = "mp_webhook_dedup_{$webhookId}";
        if (!Cache::add($dedupeKey, true, 3600)) {
            Log::info("ℹ️ [MP Webhook] Notificación duplicada detenida en frontera: {$webhookId} (type={$type})");
            return response()->json(['status' => 'ok'], 200);
        }

        // Capa 2: Dedup por ID del recurso + tipo (MP envía múltiples notificaciones distintas
        // para el mismo pago/suscripción — IPN legacy + V2 + reintentos)
        if ($dataId) {
            $resourceDedupeKey = "mp_webhook_resource_{$type}_{$dataId}";
            if (!Cache::add($resourceDedupeKey, true, 300)) {
                Log::info("ℹ️ [MP Webhook] Recurso ya procesado recientemente: {$type}/{$dataId}. Ignorando notificación redundante.");
                return response()->json(['status' => 'ok'], 200);
            }
        }

        // 4. EJECUCIÓN Y REGISTRO DE ÉXITO EN LOGS
        try {
            Log::info("🔄 [MP Webhook] Iniciando procesamiento de evento [{$type}] para ID: {$dataId}");

            switch ($type) {
                case 'payment':
                    $mpService->handlePaymentWebhook($dataId);
                    Log::info("✅ [MP Webhook] Pago procesado EXITOSAMENTE en BD. ID: {$dataId}");
                    break;

                case 'subscription_authorized_payment':
                    Log::info("ℹ️ [MP Webhook] Tópico 'subscription_authorized_payment' ignorado (gestionado por 'payment'). ID: {$dataId}");
                    break;

                default:
                    Log::info("ℹ️ [MP Webhook] Evento de tipo [{$type}] sin acción requerida. ID: {$dataId}");
                    break;
            }
        } catch (\Exception $e) {
            // Si falla por un error de base de datos o red, liberamos la llave de frontera
            // para que el reintento programado de Mercado Pago pueda volver a intentar.
            Cache::forget($dedupeKey);
            Log::error("❌ [MP Webhook] Error Crítico procesando evento [{$type}] para ID {$dataId}: " . $e->getMessage() . " - Línea: " . $e->getLine());
            
            // Aún en error interno, devolvemos 200 a MP para evitar ataques DoS por reintentos en bucle
            return response()->json(['status' => 'error_handled'], 200);
        }

        // Garantizar siempre HTTP 200 al servidor de Mercado Pago
        return response()->json(['status' => 'ok'], 200);
    }

    /**
     * Valida el encabezado x-signature de Mercado Pago devolviendo tres posibles estados:
     * 'valid', 'invalid', o 'missing_headers'.
     */
    private function verifyMercadoPagoSignature(Request $request): string
    {
        $secret = config('services.mercadopago.webhook_secret');
        if (empty($secret)) {
            return app()->environment('local') ? 'valid' : 'missing_headers';
        }

        $xSignature = $request->header('x-signature');
        $xRequestId = $request->header('x-request-id');

        if (!$xSignature || !$xRequestId) {
            return 'missing_headers';
        }

        $parts = explode(',', $xSignature);
        $ts = null;
        $hash = null;

        foreach ($parts as $part) {
            $keyValue = explode('=', trim($part), 2);
            if (count($keyValue) === 2) {
                if ($keyValue[0] === 'ts') $ts = $keyValue[1];
                if ($keyValue[0] === 'v1') $hash = $keyValue[1];
            }
        }

        if (!$ts || !$hash) {
            return 'invalid';
        }

        $dataId = $request->input('data.id') ?? $request->input('id');
        $manifest = "id:{$dataId};request-id:{$xRequestId};ts:{$ts};";
        $expectedHash = hash_hmac('sha256', $manifest, $secret);

        return hash_equals($expectedHash, $hash) ? 'valid' : 'invalid';
    }
}