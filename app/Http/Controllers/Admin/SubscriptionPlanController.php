<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @deprecated El sistema de planes de suscripción fue reemplazado por Facturación
 *             por Uso (Floor-Capped Usage Pricing) en julio 2026.
 *             La tabla subscription_plans fue eliminada.
 *             Este controlador se mantiene como stub para evitar errores 404 en el admin.
 */
class SubscriptionPlanController extends Controller
{
    public function index()
    {
        return view('admin.plans.index', ['plans' => collect()]);
    }

    public function store(Request $request)
    {
        return back()->with('info', 'El sistema de planes de suscripción ya no está operativo. Se migró a Facturación por Uso (5% de comisión).');
    }

    public function update(Request $request, $plan)
    {
        return back()->with('info', 'El sistema de planes de suscripción ya no está operativo. Se migró a Facturación por Uso (5% de comisión).');
    }

    public function toggle($plan)
    {
        return response()->json([
            'ok'       => false,
            'message'  => 'Sistema deprecado.',
        ]);
    }
}
