<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Services\EnrollmentService;
use App\Models\ClassSession;
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

            // ─── CAPA 3 ANTI-OVERBOOKING: Pre-flight check antes de MP ──────
            $selectionsBySession = collect($request->selections)->groupBy('session_id');
            $sessionIds = $selectionsBySession->keys()->toArray();

            if (!empty($sessionIds)) {
                $enrollmentService = app(EnrollmentService::class);
                $capacityInfo = $enrollmentService->getCapacityInfo($sessionIds);

                foreach ($selectionsBySession as $sessionId => $items) {
                    $requested = $items->count();
                    $available = $capacityInfo[$sessionId]['available_spots'] ?? 0;

                    if ($requested > $available) {
                        $session = ClassSession::withoutGlobalScopes()
                            ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                            ->find($sessionId);
                        $sessionName = $session ? $session->workshop->name : 'Clase #' . $sessionId;

                        Log::warning("Layer 3 bloqueo: session {$sessionId} ({$sessionName}) - solicitado {$requested}, disponible {$available}");

                        return response()->json([
                            'error'   => true,
                            'message' => "Lo sentimos, la clase \"{$sessionName}\" ya no tiene cupos suficientes. Solo quedan {$available}.",
                            'code'    => 'CLASS_FULL'
                        ], 422);
                    }
                }
            }

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