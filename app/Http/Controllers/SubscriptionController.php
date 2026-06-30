<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use App\Models\SaasPayment;
use App\Models\SubscriptionPlan;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    /**
     * Muestra la vista de gestión de suscripción con los planes disponibles.
     * Inyecta $isGracePeriod basado en la tabla independiente saas_payments.
     */
    public function index(Request $request, $subdomain)
    {
        $studio->checkAndManageLifecycle();
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $activePlans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        $countries = \App\Models\Country::orderBy('name', 'asc')->get();

        // ─── CÁLCULO DEL PERÍODO DE GRACIA ─────────────────────────────
        // Solo los pagos APPROVED cuentan: si ya fue reembolsado, no hay
        // «período de gracia» sobre un pago que ya se devolvió.
        $lastSaaSPayment = SaasPayment::where('studio_id', $studio->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        // ─── BLINDAJE ANTI-ABUSO: Detectar reembolsos recientes (180 días) ───
        $hasRecentRefund = SaasPayment::where('studio_id', $studio->id)
            ->where('status', 'refunded')
            ->where('updated_at', '>=', now()->subDays(180))
            ->exists();

        $isGracePeriod = $lastSaaSPayment
            && $lastSaaSPayment->created_at->diffInDays(now()) <= 7
            && !$hasRecentRefund;

        return view('subscriptions.index', compact(
            'studio',
            'activePlans',
            'countries',
            'isGracePeriod'
        ));
    }

    /**
     * Procesa el cambio de plan de suscripción aplicando las reglas de negocio.
     *
     * ─── REGLAS ESTRICTAS ───
     * 1. Pasar a Gratis en gracia → Cancela MP, reembolsa, downgrade INMEDIATO.
     * 2. Pasar a Gratis sin gracia → Cancela MP, next_plan_id, expires_at intacto.
     * 3. Cambio dentro de gracia (≤7 días, sin reembolsos recientes) → Cancela MP, reembolsa, genera link nuevo.
     * 4. Cambio fuera de gracia (>7 días) → Guarda next_plan_id, sin cobro hoy.
     * 5. Sin pagos SaaS previos (free → pago) → Genera link de pago directo.
     */
    public function subscribe(Request $request, Studio $studio, MercadoPagoService $mpService)
    {
        if ($studio->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para gestionar este espacio.');
        }

        $studio->checkAndManageLifecycle();

        $request->validate([
            'plan_slug'  => 'required|string|exists:subscription_plans,slug',
            'country_id' => 'required|exists:countries,id',
        ]);

        $plan = SubscriptionPlan::where('slug', $request->plan_slug)->firstOrFail();
        $isFreePlan = $plan->slug === 'free';

        // ─── LIBRO MAYOR SAAS: Buscar último cobro APPROVED ───
        // Solo pagos aprobados (no reembolsados) se consideran para el
        // período de gracia y para decidir si es un upgrade desde gratuito.
        $lastSaaSPayment = SaasPayment::where('studio_id', $studio->id)
            ->where('status', 'approved')
            ->latest()
            ->first();

        // ─── BLINDAJE ANTI-ABUSO: Detectar reembolsos recientes (180 días) ───
        $hasRecentRefund = SaasPayment::where('studio_id', $studio->id)
            ->where('status', 'refunded')
            ->where('updated_at', '>=', now()->subDays(180))
            ->exists();

        // ─── Período de gracia: ≤ 7 días desde el último cobro SaaS aprobado Y sin reembolsos recientes ───
        $isGracePeriod = $lastSaaSPayment
            && $lastSaaSPayment->created_at->diffInDays(now()) <= 7
            && !$hasRecentRefund;

        // ─── ¿Es un upgrade desde plan gratuito? (sin cobros SaaS aprobados previos) ───
        $isFirstPaidSubscription = !$lastSaaSPayment;

        // ─────────────────────────────────────────────────────────────
        // CASO 1: PASAR A PLAN GRATUITO (CANCELAR SUSCRIPCIÓN)
        //   Se divide en dos caminos según el período de gracia.
        // ─────────────────────────────────────────────────────────────
        if ($isFreePlan) {
            // Cancelar la suscripción en Mercado Pago en el acto
            if ($studio->mp_preapproval_id) {
                try {
                    $mpService->cancelPreapproval($studio->mp_preapproval_id);
                } catch (\Exception $e) {
                    Log::error('Error cancelando preapproval al migrar a gratis: ' . $e->getMessage());
                }
            }

            // ─── CAMINO A: EN GRACIA → Reembolso + downgrade INMEDIATO ───
            if ($isGracePeriod) {
                // Emitir reembolso total del último pago SaaS
                if ($lastSaaSPayment->mp_payment_id) {
                    try {
                        $mpService->refundPayment($lastSaaSPayment->mp_payment_id);

                        // Marcar el pago como reembolsado en el ledger SaaS
                        $lastSaaSPayment->update(['status' => 'refunded']);
                    } catch (\Exception $e) {
                        Log::error('Error reembolsando pago SaaS al cancelar suscripción: ' . $e->getMessage());
                    }
                }

                // Quitar beneficios INMEDIATAMENTE
                $studio->update([
                    'next_plan_id'            => null,
                    'mp_preapproval_id'       => null,
                    'subscription_status'     => 'free',
                    'subscription_plan_id'    => $plan->id,
                    'subscription_expires_at' => now(),
                ]);

                return back()->with('success',
                    'Suscripción cancelada. Se ha emitido el reembolso total y tu plan ha vuelto a ser Gratuito de inmediato.'
                );
            }

            // ─── CAMINO B: SIN GRACIA (o cooldown activo) → Intención futura ───
            // NO se toca expires_at ni el plan actual; solo se programa el downgrade.
            $studio->update([
                'next_plan_id'      => $plan->id,
                'mp_preapproval_id' => null,
            ]);

            return back()->with('success',
                'Tu plan actual estará activo hasta acabar tu período. Luego volverás al plan gratuito y no se harán más cobros automáticos.'
            );
        }

        // ─────────────────────────────────────────────────────────────
        // CASO 2: CAMBIO DENTRO DEL PERÍODO DE GRACIA (≤ 7 DÍAS)
        //   → Cancelar suscripción vieja, reembolsar, generar link nuevo
        // ─────────────────────────────────────────────────────────────
        if ($isGracePeriod) {
            // Cancelar suscripción vieja en Mercado Pago
            if ($studio->mp_preapproval_id) {
                try {
                    $mpService->cancelPreapproval($studio->mp_preapproval_id);
                } catch (\Exception $e) {
                    Log::error('Error cancelando preapproval en gracia: ' . $e->getMessage());
                }
            }

            // Emitir reembolso total del último pago SaaS
            if ($lastSaaSPayment->mp_payment_id) {
                try {
                    $mpService->refundPayment($lastSaaSPayment->mp_payment_id);

                    // Registrar el reembolso en el ledger SaaS
                    $lastSaaSPayment->update(['status' => 'refunded']);
                } catch (\Exception $e) {
                    Log::error('Error reembolsando pago SaaS en gracia: ' . $e->getMessage());
                    // No detenemos el flujo — MP puede gestionar el reembolso aparte
                }
            }

            // Limpiar next_plan_id porque el cambio se hace ahora mismo
            $studio->update([
                'next_plan_id'        => null,
                'mp_preapproval_id'   => null,
            ]);

            // Generar link de pago nuevo para iniciar el plan hoy
            try {
                $urlDePago = $mpService->createSubscriptionLink($studio, $request->plan_slug, $request->country_id);
                return redirect()->away($urlDePago);
            } catch (\Exception $e) {
                Log::error('Error generando link suscripción en gracia: ' . $e->getMessage());
                return back()->with('error', $e->getMessage() ?: 'No se pudo generar el pago en este momento. Intenta más tarde.');
            }
        }

        // ─────────────────────────────────────────────────────────────
        // CASO 3: PRIMER UPGRADE DESDE PLAN GRATUITO (sin cobros SaaS previos)
        //   → Se genera link de pago directo, sin gracia ni reembolso.
        // ─────────────────────────────────────────────────────────────
        if ($isFirstPaidSubscription) {
            $studio->update(['next_plan_id' => null]);

            try {
                $urlDePago = $mpService->createSubscriptionLink($studio, $request->plan_slug, $request->country_id);
                return redirect()->away($urlDePago);
            } catch (\Exception $e) {
                Log::error('Error generando link primer upgrade desde gratis: ' . $e->getMessage());
                return back()->with('error', $e->getMessage() ?: 'No se pudo generar el pago en este momento. Intenta más tarde.');
            }
        }

        // ─────────────────────────────────────────────────────────────
        // CASO 4: CAMBIO FUERA DE GRACIA (> 7 DÍAS) — INTENCIÓN FUTURA
        //   → Sin cobro hoy, sin link. Se cancela la renovación automática vieja.
        // ─────────────────────────────────────────────────────────────
        
        // 1. Cancelamos la suscripción vieja en Mercado Pago para que NO le cobren el plan anterior el próximo mes
        if ($studio->mp_preapproval_id) {
            try {
                $mpService->cancelPreapproval($studio->mp_preapproval_id);
            } catch (\Exception $e) {
                Log::error('Error cancelando preapproval en intención futura: ' . $e->getMessage());
            }
        }

        // 2. Guardamos el plan futuro y borramos el ID de suscripción, pero NO tocamos el expires_at
        $studio->update([
            'next_plan_id'      => $plan->id,
            'mp_preapproval_id' => null, 
        ]);

        return back()->with('success',
            'Programaremos tu cambio. Disfruta tu plan actual hasta acabar tu período; luego te enviaremos el link de pago para iniciar este nuevo plan.'
        );
    }
}
