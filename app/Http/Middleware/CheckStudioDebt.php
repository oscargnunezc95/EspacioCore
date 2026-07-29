<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\StudioInvoice;
use Carbon\Carbon;

class CheckStudioDebt
{
    /**
     * Rutas exentas del bloqueo por deuda.
     * El estudio debe poder ver su facturación y pagar sus facturas
     * aunque esté en mora.
     */
    protected array $exemptRoutes = [
        'account.billing',        // Página de facturación (ver facturas)
        'account.billing.pay',    // Endpoint de pago de factura
        'account.index',          // Página de cuenta (para navegar)
        'account.mp.disconnect',  // Permitir desconectar MP
        'account.mp.setup-static-qr',
        'logout',                 // Permitir cerrar sesión
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $studioId = config('tenant.studio_id');

        if (!$studioId) {
            return $next($request);
        }

        // ═══════════════════════════════════════════════════════════
        // 1. ACTUALIZACIÓN AUTOMÁTICA AL DÍA 6
        //    Si ya pasó la fecha límite del día 5, marcar facturas
        //    pendientes como vencidas.
        // ═══════════════════════════════════════════════════════════
        $this->markPastDueIfNeeded($studioId);

        // ═══════════════════════════════════════════════════════════
        // 2. VERIFICAR SI HAY DEUDA VENCIDA
        // ═══════════════════════════════════════════════════════════
        $hasPastDue = StudioInvoice::where('studio_id', $studioId)
            ->where('status', 'past_due')
            ->exists();

        if (!$hasPastDue) {
            // Sin deuda → continuar normal
            return $next($request);
        }

        // ═══════════════════════════════════════════════════════════
        // 3. BLOQUEO POR DEUDA
        // ═══════════════════════════════════════════════════════════

        $routeName = $request->route()->getName();

        // Las rutas exentas siempre pasan
        if (in_array($routeName, $this->exemptRoutes, true)) {
            return $next($request);
        }

        // Bloquear mutaciones (POST, PUT, PATCH, DELETE) → 403
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return response()->json([
                'error'   => 'studio_blocked',
                'message' => 'Tu estudio tiene facturas vencidas. Por favor, regulariza tu situación desde la sección de Facturación.',
            ], 403);
        }

        // Redirigir GET a la vista de bloqueo
        if ($request->method() === 'GET') {
            $pastDueInvoices = StudioInvoice::where('studio_id', $studioId)
                ->where('status', 'past_due')
                ->orderBy('billing_period', 'asc')
                ->get();

            $totalDebt = $pastDueInvoices->sum('total_due');

            $studioModel = \App\Models\Studio::find($studioId);

            return response()->view('account.billing.locked', [
                'studio'           => $studioModel,
                'pastDueInvoices'  => $pastDueInvoices,
                'totalDebt'        => $totalDebt,
                'subdomain'        => $studioModel?->subdomain ?? $request->route('subdomain'),
            ], 402); // 402 Payment Required
        }

        return $next($request);
    }

    /**
     * Si la fecha actual superó el día 5 del mes y hay facturas 'pending',
     * las marca automáticamente como 'past_due'.
     */
    protected function markPastDueIfNeeded(int $studioId): void
    {
        $now = Carbon::now();

        // Buscar facturas pending cuya due_date ya pasó
        $overdueInvoices = StudioInvoice::where('studio_id', $studioId)
            ->where('status', 'pending')
            ->where('due_date', '<', $now)
            ->get();

        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'past_due']);
            \Illuminate\Support\Facades\Log::info("🚫 Factura #{$invoice->id} del studio #{$studioId} marcada como past_due. Período: {$invoice->billing_period}");
        }
    }
}
