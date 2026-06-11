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
    /**
     * Actualiza el Plan asignado a un estudio vía AJAX y notifica al dueño.
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
            $planName = 'Plan Gratis (5%)'; // Valor por defecto amigable para el correo

            if ($newPlanId) {
                $plan = SubscriptionPlan::find($newPlanId);
                $newStatus = $plan->slug;
                $planName = $plan->name;
            }

            // Actualización integral de todas las columnas SaaS vinculadas
            $studio->update([
                'subscription_plan_id' => $newPlanId,
                'subscription_status'  => $newStatus, 
                'billing_cycles_count' => 0,          
            ]);

            // =========================================================================
            // LÓGICA DE NOTIFICACIONES (In-App y Email)
            // =========================================================================
            $studio->load('user'); // Aseguramos que el usuario dueño esté cargado en memoria

            if ($studio->user) {
                try {
                    // 1. Notificación In-App (Campanita)
                    // Reutilizamos la notificación existente de Mercado Pago pasando el nuevo estado
                    $studio->user->notify(new \App\Notifications\SaaSSubscriptionNotification($studio, $newStatus));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error enviando notificación in-app de cambio manual de plan: ' . $e->getMessage());
                }

                try {
                    // 2. Correo Electrónico Informativo
                    // Asume que crearás un Mailable llamado PlanManuallyUpdatedMail
                    \Illuminate\Support\Facades\Mail::to($studio->user->email)
                        ->queue(new \App\Mail\PlanManuallyUpdatedMail($studio, $planName));
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Error encolando correo de cambio manual de plan: ' . $e->getMessage());
                }
            }
        }

        // Forzamos la recarga de la relación para la respuesta JSON
        $studio->load('subscriptionPlan');
        $finalPlanName = $studio->subscriptionPlan ? $studio->subscriptionPlan->name : 'Gratis (5%)';

        return response()->json([
            'ok'      => true,
            'message' => "El estudio ahora pertenece al plan: {$finalPlanName}",
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