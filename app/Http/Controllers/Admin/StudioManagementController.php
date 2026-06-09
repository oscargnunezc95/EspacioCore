<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudioManagementController extends Controller
{
    public function index(): View
    {
        // Traemos los estudios y los planes disponibles para el selector
        $studios = Studio::withoutGlobalScopes()
            ->with([
                'user' => fn($q) => $q->select('id', 'name', 'email'),
                'subscriptionPlan' // Eager loading del plan
            ])
            ->orderBy('name')
            ->get();

        $plans = SubscriptionPlan::where('is_active', true)->orderBy('price')->get();

        return view('admin.studios.index', compact('studios', 'plans'));
    }

    /**
     * Actualiza el Plan asignado a un estudio vía AJAX.
     */
    public function updatePlan(Request $request, Studio $studio): JsonResponse
    {
        $validated = $request->validate([
            // Puede ser null si quieres pasarlo al "Plan por Defecto (5%)"
            'subscription_plan_id' => ['nullable', 'exists:subscription_plans,id'],
        ]);

        $newPlanId = $validated['subscription_plan_id'];

        // Auditoría: Solo actuamos si realmente se está cambiando a un plan distinto
        if ($studio->subscription_plan_id !== $newPlanId) {
            
            // Determinamos el nuevo slug de estado
            $newStatus = 'free'; 
            if ($newPlanId) {
                $plan = SubscriptionPlan::find($newPlanId);
                $newStatus = $plan->slug; // Ej: 'founder-elite', 'pro'
            }

            // Actualización integral de todas las columnas SaaS vinculadas
            $studio->update([
                'subscription_plan_id' => $newPlanId,
                'subscription_status'  => $newStatus, // Mantenemos la Única Fuente de Verdad
                'billing_cycles_count' => 0,          // Reinicio crítico del contador de meses
            ]);
        }

        // Forzamos la recarga de la relación para evitar caché de Eloquent en el JSON
        $studio->load('subscriptionPlan');
        
        $planName = $studio->subscriptionPlan ? $studio->subscriptionPlan->name : 'Starter (5%)';

        return response()->json([
            'ok'      => true,
            'message' => "El estudio ahora pertenece al plan: {$planName}",
        ]);
    }
    /**
     * Muestra el Ledger (Auditoría de ingresos y comisiones) de un estudio específico.
     */
    public function audit(Request $request, Studio $studio): View
    {
        // Por defecto mostramos el mes actual si no hay filtro
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        
        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();

        // Construimos la consulta base (Solo Mercado Pago y Aprobados)
        $paymentsQuery = \App\Models\Payment::with(['student.user']) // Eager loading ligero para ver quién pagó
            ->where('studio_id', $studio->id)
            ->where('payment_method', 'mercadopago')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate]);

        // Clonamos la consulta para sacar los totales matemáticos sin afectar el paginador
        $grossSales = (clone $paymentsQuery)->sum('amount');
        $platformFee = (clone $paymentsQuery)->sum('platform_fee');
        $netTransfer = $grossSales - $platformFee;

        $totals = (object) [
            'gross_sales'  => $grossSales,
            'platform_fee' => $platformFee,
            'net_transfer' => $netTransfer,
        ];

        // Obtenemos los registros paginados para la tabla del Ledger
        $payments = $paymentsQuery->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        return view('admin.studios.audit', compact('studio', 'selectedMonth', 'totals', 'payments'));
    }
}