<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;
use App\Services\MercadoPagoService;

class SubscriptionController extends Controller
{
    /**
     * Muestra la vista de gestión de suscripción con los planes disponibles.
     */
    public function index(Request $request, $subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $activePlans = \App\Models\SubscriptionPlan::where('is_active', true)
            ->orderBy('price')
            ->get();

        $countries = \App\Models\Country::orderBy('name', 'asc')->get();

        return view('subscriptions.index', compact('studio', 'activePlans', 'countries'));
    }

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
            'plan_slug'  => 'required|string|exists:subscription_plans,slug',
            'country_id' => 'required|exists:countries,id',
        ]);

        try {
            $urlDePago = $mpService->createSubscriptionLink($studio, $request->plan_slug, $request->country_id);
            return redirect()->away($urlDePago);
            
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error generando link suscripción: ' . $e->getMessage());
            return back()->with('error', $e->getMessage() ?: 'No se pudo generar el pago en este momento. Intenta más tarde.');
        }
    }
}
