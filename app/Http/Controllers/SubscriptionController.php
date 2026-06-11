<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use App\Services\MercadoPagoService;

class SubscriptionController extends Controller
{
    /**
     * Inicia el proceso de pago redirigiendo a Mercado Pago.
     * El controlador solo valida el Request y delega toda la lógica al servicio.
     */
    public function subscribe(Request $request, Studio $studio, MercadoPagoService $mpService)
    {
        if ($studio->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para gestionar este espacio.');
        }

        $request->validate([
            'plan_slug' => 'required|string|exists:subscription_plans,slug',
        ]);

        try {
            $urlDePago = $mpService->createSubscriptionLink($studio, $request->plan_slug);
            return redirect()->away($urlDePago);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generando link suscripción: ' . $e->getMessage());
            return back()->with('error', $e->getMessage() ?: 'No se pudo generar el pago en este momento. Intenta más tarde.');
        }
    }
}
