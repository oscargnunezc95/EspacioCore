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
        // 1. Autorización: Solo el dueño puede pagar por este estudio
        if ($studio->user_id !== $request->user()->id) {
            abort(403, 'No tienes permiso para gestionar la facturación de este espacio.');
        }

        // 2. Validación del plan elegido
        $request->validate([
            'plan' => 'required|string|in:pro,elite',
        ]);

        // 3. Mapeo de precios
        $prices = [
            'pro' => 45000,
            'elite' => 89000,
        ];
        
        $planName = ucfirst($request->plan);
        $price = $prices[$request->plan];

        // 4. Ejecución
        try {
            $urlDePago = $mpService->createSubscriptionLink($studio, $planName, $price);
            return redirect()->away($urlDePago);
            
        } catch (Exception $e) {
            return back()->with('error', 'No se pudo generar el pago en este momento. Intenta más tarde.');
        }
    }
}