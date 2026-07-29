<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StandarMinimumFloor;
use App\Models\StudioInvoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillingController extends Controller
{
    /**
     * Vista principal del panel de facturación.
     * Muestra el piso estándar configurable, facturas pendientes (editables)
     * y facturas pagadas (solo lectura).
     */
    public function index(): View
    {
        $standardFloor = StandarMinimumFloor::current();

        $pendingInvoices = StudioInvoice::with('studio')
            ->whereIn('status', ['pending', 'past_due'])
            ->orderBy('billing_period', 'desc')
            ->get();

        $paidInvoices = StudioInvoice::with('studio')
            ->where('status', 'paid')
            ->orderBy('billing_period', 'desc')
            ->take(50)
            ->get();

        return view('admin.billing.index', compact(
            'standardFloor',
            'pendingInvoices',
            'paidInvoices'
        ));
    }

    /**
     * Actualiza el valor del piso mínimo estándar (AJAX).
     */
    public function updateStandardFloor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'value' => ['required', 'integer', 'min:0', 'max:99999999'],
        ]);

        StandarMinimumFloor::updateValue($validated['value']);

        return response()->json([
            'ok'      => true,
            'message' => 'Piso mínimo estándar actualizado a $' . number_format($validated['value'], 0, ',', '.') . '.',
            'value'   => $validated['value'],
        ]);
    }

    /**
     * Actualiza el minimum_floor de una factura individual (AJAX).
     * Solo permite modificar facturas no pagadas.
     */
    public function updateInvoiceFloor(Request $request, StudioInvoice $invoice): JsonResponse
    {
        if ($invoice->isPaid()) {
            return response()->json([
                'ok'      => false,
                'message' => 'No se puede modificar una factura ya pagada.',
            ], 422);
        }

        $validated = $request->validate([
            'minimum_floor' => ['required', 'integer', 'min:0', 'max:99999999'],
        ]);

        $invoice->update(['minimum_floor' => $validated['minimum_floor']]);

        return response()->json([
            'ok'      => true,
            'message' => "Piso mínimo de factura #{$invoice->id} actualizado a $" . number_format($validated['minimum_floor'], 0, ',', '.') . '.',
            'value'   => $validated['minimum_floor'],
        ]);
    }
}
