<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use App\Services\MercadoPagoService;
use Exception;

class SubscriptionController extends Controller
{
    /**
     * Inicia el proceso de pago redirigiendo a Mercado Pago.
     */
    public function subscribe(Request $request, Studio $studio, MercadoPagoService $mpService)
    {
        if ($studio->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para gestionar este espacio.');
        }

        $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
        ]);

        $plan = \App\Models\SubscriptionPlan::where('slug', $request->plan_slug)
            ->where('is_active', true)
            ->firstOrFail();

        // 1. Validación estricta de cupos (Capacity Limit)
        if ($plan->capacity_limit !== null) {
            $currentSubscribers = \App\Models\Studio::where('subscription_plan_id', $plan->id)
                ->whereIn('subscription_status', ['pro', 'elite', 'past_due'])
                ->count();

            if ($currentSubscribers >= $plan->capacity_limit) {
                return back()->with('error', 'Lo sentimos, los cupos para este plan se han agotado.');
            }
        }

        // 2. Auditoría: Si está cambiando a un plan distinto, reiniciamos el contador de ciclos
        // (Guardamos el plan en BD ahora para que el Webhook ya sepa de qué plan viene el pago)
        if ($studio->subscription_plan_id !== $plan->id) {
            $studio->update([
                'subscription_plan_id' => $plan->id,
                'billing_cycles_count' => 0 // Reinicio crítico
            ]);
        }

        // 3. Generar pago
        try {
            // Nota: Debes adaptar tu método createSubscriptionLink para que reciba $plan->price y $plan->name
            $urlDePago = $mpService->createSubscriptionLink($studio, $plan->name, $plan->price);
            return redirect()->away($urlDePago);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generando link suscripción: ' . $e->getMessage());
            return back()->with('error', 'No se pudo generar el pago en este momento. Intenta más tarde.');
        }
    }
}