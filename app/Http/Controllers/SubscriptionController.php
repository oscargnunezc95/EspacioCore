<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Studio;

/**
 * @deprecated El sistema de suscripciones SaaS fue reemplazado por Facturación
 *             Mensual por Uso (Floor-Capped Usage Pricing) en julio 2026.
 *             Este controlador redirige a la nueva sección de facturación.
 */
class SubscriptionController extends Controller
{
    /**
     * Redirige a la nueva página de facturación.
     */
    public function index(Request $request, $subdomain)
    {
        return redirect()->route('account.billing', $subdomain)
            ->with('info', 'El sistema de suscripciones ha sido reemplazado por Facturación por Uso. Aquí puedes ver tus facturas mensuales.');
    }

    /**
     * @deprecated El sistema de suscripciones SaaS ya no está operativo.
     */
    public function subscribe(Request $request, Studio $studio)
    {
        return redirect()->route('account.billing', $studio->subdomain)
            ->with('info', 'El sistema de suscripciones ha sido reemplazado. Ahora pagas solo una comisión del 5% sobre tus ventas mensuales.');
    }

    /**
     * @deprecated El sistema de suscripciones SaaS ya no está operativo.
     */
    public function retryPayment(Request $request, Studio $studio)
    {
        return redirect()->route('account.billing', $studio->subdomain)
            ->with('info', 'El pago de suscripción ya no es necesario. Revisa tu panel de facturación para ver tus comisiones mensuales.');
    }
}
