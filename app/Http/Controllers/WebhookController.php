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

        if ($studioId && $studio = \App\Models\Studio::with('subscriptionPlan')->find($studioId)) {
            
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
                    $newExpiration = \Carbon\Carbon::parse($currentExpiration)->addMonth();
                } else {
                    $newExpiration = now()->addMonth();
                }

                // 2. Incrementamos el ciclo actual
                $currentCycle = $studio->billing_cycles_count + 1;

                // 3. Actualización 100% dinámica
                $studio->update([
                    'mp_preapproval_id' => $dataId,
                    'subscription_status' => $planSlug, // Ahora guarda 'founder-elite', 'pro', etc.
                    'subscription_expires_at' => $newExpiration,
                    'billing_cycles_count' => $currentCycle
                ]);

                // 4. Evaluación del Límite de Vida del Plan (Sunsetting)
                if ($plan && $plan->max_billing_cycles && $currentCycle >= $plan->max_billing_cycles) {
                    
                    // A. Cancelar cobro automático en Mercado Pago
                    try {
                        $mpService->cancelPreapproval($dataId);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Error cancelando suscripción SaaS en MP: " . $e->getMessage());
                    }
                    
                    // B. Correo de fin de beneficio
                    \Illuminate\Support\Facades\Mail::to($studio->user->email)
                        ->queue(new \App\Mail\PlanLifecycleEndingMail($studio));
                        
                } else {
                    // Recibo normal dinámico
                    \Illuminate\Support\Facades\Mail::to($studio->user->email)
                        ->queue(new \App\Mail\SubscriptionReceiptMail($studio, $planName, $planPrice));
                }

                // Alerta interna para la plataforma dinámica
                $adminEmail = env('ADMIN_NOTIFICATION_EMAIL', 'admin@estadoprisma.test');
                \Illuminate\Support\Facades\Mail::to($adminEmail)
                    ->queue(new \App\Mail\NewSubscriptionAlertMail($studio, $planName));

            } elseif (in_array($status, ['paused', 'cancelled'])) {
                // Limpiamos el ID, se mantienen los beneficios hasta que expire el Cron Job
                $studio->update(['mp_preapproval_id' => null]);
            }

            try {
                if ($studio->user) {
                    $studio->user->notify(new \App\Notifications\SaaSSubscriptionNotification($studio, $status));
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error registrando notificación in-app de suscripción SaaS: ' . $e->getMessage());
            }
        }
    }
}