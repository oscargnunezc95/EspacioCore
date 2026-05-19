<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckoutController extends Controller
{
    /**
     * Recibe la petición del Carrito y devuelve el init_point de MP
     */
    public function generarCheckout(Request $request, MercadoPagoService $mpService)
    {
        // 1. Validación de seguridad estricta
        $request->validate([
            'studio_id' => ['required', 'integer', 'exists:studios,id'],
            'session_ids' => ['required', 'array'],
            'session_ids.*' => ['integer', 'exists:class_sessions,id']
        ]);

        try {
            $user = Auth::user(); // Tomamos al alumno logueado
            
            // 2. Delegamos la lógica financiera al Servicio
            $preference = $mpService->createPreference(
                $request->studio_id, 
                $request->session_ids, 
                $user
            );

            // 3. Devolvemos el link seguro (init_point) al Javascript
            return response()->json([
                'init_point' => $preference['init_point']
            ]);

        } catch (\Throwable $e) {
            Log::error('Error en Checkout: ' . $e->getMessage() . ' Línea: ' . $e->getLine());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}