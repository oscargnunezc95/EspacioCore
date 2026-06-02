<?php

namespace App\Services;

use App\Models\Student;
use App\Models\ClassSession;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    public function getDashboardMetrics(string $period): array
    {
        $parsedDate = Carbon::createFromFormat('Y-m', $period);
        $month = $parsedDate->month;
        $year = $parsedDate->year;

        return [
            'period' => $period,
            'activeStudentsCount' => $this->getActiveStudentsCount(),
            'newStudentsCount' => $this->getNewStudentsCount($year, $month),
            'occupancyData' => $this->getOccupancyMetrics($year, $month),
            'financialData' => $this->getFinancialMetrics($year, $month),
            'todayClasses' => $this->getTodayClasses(),
            'studentsWithDebt' => $this->getStudentsWithDebt(),
            'historicalData' => $this->getHistoricalMetrics($year, $month), // <--- NUEVO
        ];
    }

    private function getActiveStudentsCount(): int
    {
        return Student::where('is_guest', false)->count();
    }

    private function getNewStudentsCount(int $year, int $month): int
    {
        return Student::where('is_guest', false)
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
    }

    private function getOccupancyMetrics(int $year, int $month): array
    {
        $sessions = ClassSession::with('workshop')
            ->withCount('students')
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('is_cancelled', false)
            ->get();

        $totalCapacity = $sessions->sum(fn($s) => $s->workshop->capacity ?? 0);
        $totalEnrolled = $sessions->sum('students_count');

        return [
            // Se eliminó monthClassesCount
            'occupancyRate' => $totalCapacity > 0 ? round(($totalEnrolled / $totalCapacity) * 100) : 0,
        ];
    }

    private function getFinancialMetrics(int $year, int $month): array
    {
        $totalsByMethod = Payment::select('payment_method', DB::raw('SUM(amount) as total'))
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->groupBy('payment_method')
            ->pluck('total', 'payment_method')
            ->mapWithKeys(fn ($total, $method) => [strtolower($method) => $total]);

        $monthlyRevenue = Payment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('amount');

        // Nómina del mes (pagos a profesores con status paid)
        $monthlyPayroll = \App\Models\TeacherPayment::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('status', 'paid')
            ->sum('amount');

        $netMargin = $monthlyRevenue - $monthlyPayroll;

        $onlineMethods = ['online', 'mercadopago', 'pasarela de pago'];
        $revenueByMethod = [
            'online' => 0,
            'efectivo' => $totalsByMethod->get('efectivo', 0),
            'transferencia' => $totalsByMethod->get('transferencia', 0),
        ];

        foreach ($totalsByMethod as $method => $total) {
            if (in_array($method, $onlineMethods)) {
                $revenueByMethod['online'] += $total;
            } elseif (!in_array($method, ['efectivo', 'transferencia'])) {
                $revenueByMethod['transferencia'] += $total;
            }
        }

        return [
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyPayroll' => $monthlyPayroll,
            'netMargin'      => $netMargin,
            'revenuePercentages' => [
                'online' => $monthlyRevenue > 0 ? round(($revenueByMethod['online'] / $monthlyRevenue) * 100) : 0,
                'transferencia' => $monthlyRevenue > 0 ? round(($revenueByMethod['transferencia'] / $monthlyRevenue) * 100) : 0,
                'efectivo' => $monthlyRevenue > 0 ? round(($revenueByMethod['efectivo'] / $monthlyRevenue) * 100) : 0,
            ],
        ];
    }

    private function getTodayClasses()
    {
        return ClassSession::with(['workshop.teacher'])
            ->withCount('students')
            ->whereDate('date', now()->toDateString())
            ->orderBy('start_time', 'asc')
            ->get();
    }

    private function getStudentsWithDebt()
    {
        return Student::whereHas('classSessions', function ($query) {
            $query->where('class_session_student.payment_status', 'pending')
                  ->where('date', '<=', now()->toDateString())
                  ->where('is_cancelled', false);
        })->with(['classSessions' => function ($query) {
            $query->where('class_session_student.payment_status', 'pending')
                  ->where('date', '<=', now()->toDateString())
                  ->where('is_cancelled', false)
                  ->orderBy('date', 'desc')
                  ->with('workshop');
        }])->get();
    }

    private function getHistoricalMetrics(int $year, int $month): array
    {
        $labels = [];
        $newStudentsData = [];
        
        // Estructura segmentada para los ingresos
        $revenueData = [
            'online' => [],
            'transferencia' => [],
            'efectivo' => []
        ];

        $onlineMethods = ['online', 'mercadopago', 'pasarela de pago'];

        // Generar datos para los últimos 3 meses
        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::create($year, $month, 1)->subMonths($i);
            $y = $date->year;
            $m = $date->month;

            $labels[] = ucfirst($date->translatedFormat('F')); // Ej: "Mayo"

            // 1. Agrupar pagos del mes iterado directamente en base de datos
            $totalsByMethod = Payment::select('payment_method', DB::raw('SUM(amount) as total'))
                ->whereYear('created_at', $y)
                ->whereMonth('created_at', $m)
                ->groupBy('payment_method')
                ->pluck('total', 'payment_method')
                ->mapWithKeys(fn ($total, $method) => [strtolower($method) => $total]);

            // 2. Clasificar los totales de ese mes
            $monthOnline = 0;
            $monthTransferencia = 0;
            $monthEfectivo = $totalsByMethod->get('efectivo', 0);

            foreach ($totalsByMethod as $method => $total) {
                if (in_array($method, $onlineMethods)) {
                    $monthOnline += $total;
                } elseif (!in_array($method, ['efectivo', 'transferencia'])) {
                    $monthTransferencia += $total;
                } else {
                    if ($method === 'transferencia') {
                        $monthTransferencia += $total;
                    }
                }
            }

            // 3. Guardar en los arreglos históricos
            $revenueData['online'][] = $monthOnline;
            $revenueData['transferencia'][] = $monthTransferencia;
            $revenueData['efectivo'][] = $monthEfectivo;

            // Alumnas
            $newStudentsData[] = Student::where('is_guest', false)
                ->whereYear('created_at', $y)
                ->whereMonth('created_at', $m)
                ->count();
        }

        return [
            'labels' => $labels,
            'revenue' => $revenueData,
            'newStudents' => $newStudentsData,
        ];
    }
}