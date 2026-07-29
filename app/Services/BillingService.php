<?php

namespace App\Services;

use App\Models\StandarMinimumFloor;
use App\Models\Studio;
use App\Models\StudioInvoice;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillingService
{
    /**
     * Genera la factura mensual para un estudio específico en un período dado.
     *
     * Algoritmo Floor-Capped Usage Pricing:
     * - Piso mínimo ($15.000) para todos los estudios.
     * - Comisión del 5% sobre ventas brutas válidas.
     * - Founder: el piso actúa como TECHO (la comisión nunca supera los $15.000).
     * - Regular: el piso actúa como MÍNIMO (si la comisión es menor, paga el piso).
     *
     * @param  Studio  $studio  El estudio a facturar.
     * @param  Carbon  $period  Primer día del mes a evaluar (ej: 2026-06-01).
     * @return StudioInvoice
     */
    public function generateInvoice(Studio $studio, Carbon $period): StudioInvoice
    {
        $billingPeriod = $period->format('Y-m');
        $monthStart = $period->copy()->startOfMonth();
        $monthEnd   = $period->copy()->endOfMonth();

        // ═══════════════════════════════════════════════════════════════
        // 1. CÁLCULO DE VENTAS BRUTAS (gross_sales)
        //    Sumar amount de payments del estudio en el mes evaluado.
        //    EXCLUIR: refunded_overbooking, cancelled, refunded.
        // ═══════════════════════════════════════════════════════════════
        $grossSales = (int) Payment::where('studio_id', $studio->id)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereNotIn('status', ['refunded_overbooking', 'cancelled', 'refunded'])
            ->sum('amount');

        // ═══════════════════════════════════════════════════════════════
        // 2. PRORRATEO DEL PISO MÍNIMO (minimum_floor)
        //    Si el estudio fue creado DENTRO del mes evaluado → prorrateo.
        //    Si fue creado antes → piso completo de $15.000.
        // ═══════════════════════════════════════════════════════════════
        $minimumFloor = $this->calculateMinimumFloor($studio, $period);

        // ═══════════════════════════════════════════════════════════════
        // 3. CÁLCULO DE COMISIÓN (5% redondeado)
        // ═══════════════════════════════════════════════════════════════
        $calculatedCommission = (int) round($grossSales * 0.05);

        // ═══════════════════════════════════════════════════════════════
        // 4. ALGORITMO FLOOR-CAPPED
        // ═══════════════════════════════════════════════════════════════
        $isFounder = $studio->is_founder && $studio->founder_cycles_remaining > 0;

        if ($isFounder) {
            // ── FOUNDER: El piso es un TECHO ──────────────────────────
            if ($calculatedCommission <= $minimumFloor) {
                // Comisión baja → paga el piso (sin ahorro)
                $totalDue      = $minimumFloor;
                $founderSavings = 0;
            } else {
                // Comisión alta → el beneficio founder la CAPEA al piso
                $totalDue      = $minimumFloor;
                $founderSavings = $calculatedCommission - $minimumFloor;
            }
        } else {
            // ── REGULAR: El piso es un MÍNIMO ─────────────────────────
            $founderSavings = 0;
            if ($calculatedCommission <= $minimumFloor) {
                $totalDue = $minimumFloor;
            } else {
                $totalDue = $calculatedCommission;
            }
        }

        // ═══════════════════════════════════════════════════════════════
        // 5. PERSISTENCIA DE LA FACTURA
        //    due_date = día 5 del mes en curso a las 23:59:59
        // ═══════════════════════════════════════════════════════════════
        $dueDate = Carbon::now()->startOfMonth()->addDays(4)->endOfDay();

        $invoice = StudioInvoice::create([
            'studio_id'            => $studio->id,
            'billing_period'       => $billingPeriod,
            'gross_sales'          => $grossSales,
            'calculated_commission' => $calculatedCommission,
            'minimum_floor'        => $minimumFloor,
            'founder_savings'      => $founderSavings,
            'total_due'            => $totalDue,
            'status'               => 'pending',
            'due_date'             => $dueDate,
        ]);

        Log::info("📊 Factura generada: Studio #{$studio->id} ({$studio->name}) — Período {$billingPeriod}", [
            'gross_sales'          => $grossSales,
            'calculated_commission' => $calculatedCommission,
            'minimum_floor'        => $minimumFloor,
            'founder_savings'      => $founderSavings,
            'total_due'            => $totalDue,
            'is_founder'           => $isFounder,
            'cycles_remaining'     => $studio->founder_cycles_remaining,
        ]);

        return $invoice;
    }

    /**
     * Calcula el piso mínimo prorrateado si el estudio fue creado durante
     * el mes evaluado. Fórmula:
     *
     *   minimum_floor = round(15000 * (D - d + 1) / D)
     *
     * donde:
     *   D = días totales del mes evaluado
     *   d = día del mes en que se creó el estudio (1-indexed)
     *
     * Si el estudio fue creado antes del mes evaluado → piso completo.
     */
    public function calculateMinimumFloor(Studio $studio, Carbon $period): int
    {
        $baseFloor = StandarMinimumFloor::current();

        $studioCreatedAt = Carbon::parse($studio->created_at);
        $monthStart = $period->copy()->startOfMonth();
        $monthEnd   = $period->copy()->endOfMonth();
        $totalDays  = $monthStart->daysInMonth;

        // Si el estudio fue creado antes de este mes → piso completo
        if ($studioCreatedAt->lt($monthStart)) {
            return $baseFloor;
        }

        // Si el estudio fue creado después de este mes → no debería ocurrir,
        // pero por seguridad retornamos piso completo.
        if ($studioCreatedAt->gt($monthEnd)) {
            return $baseFloor;
        }

        // Prorrateo: días activos en el mes
        $creationDay = (int) $studioCreatedAt->day;
        $activeDays  = $totalDays - $creationDay + 1;

        return (int) round($baseFloor * $activeDays / $totalDays);
    }

    /**
     * Proyección en tiempo real para el mes en curso.
     * Útil para mostrar al estudio cómo va su factura antes del cierre.
     *
     * @return array{gross_sales: int, projected_commission: int, projected_minimum_floor: int, is_founder: bool, projected_savings: int, projected_total: int}
     */
    public function getCurrentMonthProjection(Studio $studio): array
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $grossSales = (int) Payment::where('studio_id', $studio->id)
            ->whereBetween('created_at', [$monthStart, $monthEnd])
            ->whereNotIn('status', ['refunded_overbooking', 'cancelled', 'refunded'])
            ->sum('amount');

        $minimumFloor = $this->calculateMinimumFloor($studio, $now);
        $projectedCommission = (int) round($grossSales * 0.05);

        $isFounder = $studio->is_founder && $studio->founder_cycles_remaining > 0;

        if ($isFounder) {
            $projectedSavings = max(0, $projectedCommission - $minimumFloor);
            $projectedTotal = $minimumFloor;
        } else {
            $projectedSavings = 0;
            $projectedTotal = max($minimumFloor, $projectedCommission);
        }

        return [
            'gross_sales'           => $grossSales,
            'projected_commission'  => $projectedCommission,
            'projected_minimum_floor' => $minimumFloor,
            'is_founder'            => $isFounder,
            'projected_savings'     => $projectedSavings,
            'projected_total'       => $projectedTotal,
        ];
    }
}
