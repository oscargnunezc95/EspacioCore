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
        $request->validate([
            'studio_id' => ['required', 'integer', 'exists:studios,id'],
            'selections' => ['required', 'array'],
            'selections.*.session_id' => ['required', 'integer'],
            'selections.*.student_id' => ['required', 'integer']
        ]);

        try {
            $user = Auth::user(); 
            
            $preference = $mpService->createPreference(
                $request->studio_id, 
                $request->selections, 
                $user
            );

            return response()->json(['init_point' => $preference['init_point']]);

        } catch (\Throwable $e) {
            Log::error('Error generando checkout: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar el pago.'], 500);
        }
    }
}