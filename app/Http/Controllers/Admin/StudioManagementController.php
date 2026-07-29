<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Studio;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudioManagementController extends Controller
{
    public function index(): View
    {
        $studios = Studio::withoutGlobalScopes()
            ->with([
                'user' => fn($q) => $q->select('id', 'name', 'email'),
                'invoices' => fn($q) => $q->latest('billing_period')->limit(3),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.studios.index', compact('studios'));
    }

    /**
     * Actualiza el estado Founder de un estudio vía AJAX.
     * El sistema de planes de suscripción fue reemplazado por
     * Facturación por Uso (Floor-Capped Usage Pricing).
     */
    public function updatePlan(Request $request, Studio $studio): JsonResponse
    {
        $validated = $request->validate([
            'is_founder'              => ['boolean'],
            'founder_cycles_remaining' => ['integer', 'min:0', 'max:12'],
        ]);

        $studio->update($validated);

        $status = $studio->isFounderActive()
            ? "👑 Founder activo ({$studio->founder_cycles_remaining} ciclos restantes)"
            : 'Estudio regular (sin beneficio Founder)';

        return response()->json([
            'ok'      => true,
            'message' => "Estudio actualizado: {$status}",
        ]);
    }

    /**
     * Muestra el Ledger (Auditoría de ingresos y comisiones) de un estudio específico.
     */
    public function audit(Request $request, Studio $studio): View
    {
        $selectedMonth = $request->input('month', now()->format('Y-m'));

        $startDate = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        $endDate   = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->endOfMonth();

        $paymentsQuery = \App\Models\Payment::with(['student.user'])
            ->where('studio_id', $studio->id)
            ->where('payment_method', 'mercadopago')
            ->where('status', 'approved')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $grossSales   = (clone $paymentsQuery)->sum('amount');
        $platformFee  = (clone $paymentsQuery)->sum('platform_fee');
        $netTransfer  = $grossSales - $platformFee;

        $totals = (object) [
            'gross_sales'  => $grossSales,
            'platform_fee' => $platformFee,
            'net_transfer' => $netTransfer,
        ];

        $payments = $paymentsQuery->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        // Obtener facturas del estudio para el período
        $invoices = $studio->invoices()
            ->where('billing_period', $selectedMonth)
            ->get();

        return view('admin.studios.audit', compact('studio', 'selectedMonth', 'totals', 'payments', 'invoices'));
    }
}
