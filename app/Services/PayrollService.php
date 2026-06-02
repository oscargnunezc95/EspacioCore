<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\ClassSession;
use App\Models\TeacherPayment;
use Carbon\Carbon;

class PayrollService
{
    /**
     * Obtiene las ClassSessions completadas de un profesor en un mes,
     * con sus asistencias y un subtotal sugerido.
     *
     * @return array{teacher: Teacher, sessions: \Illuminate\Support\Collection, subtotal: int, month_year: string, payments: \Illuminate\Support\Collection}
     */
    public function getMonthlyReport(int $teacherId, string $monthYear): array
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        $date = Carbon::createFromFormat('Y-m', $monthYear);

        // Sesiones del mes de este profesor (solo las pasadas, no canceladas)
        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => fn($q) => $q->withoutGlobalScopes(),
                'workshop.teacher',
                'attendances',
                'schedule',
            ])
            ->whereHas('workshop', function ($query) use ($teacherId) {
                $query->withoutGlobalScopes()->where('teacher_id', $teacherId);
            })
            ->where('is_cancelled', false)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            ->where('date', '<=', now()->toDateString()) // Solo sesiones ya ocurridas
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Calcular subtotal sugerido: sumar la cantidad de asistencias * un valor base
        // (puede ajustarse por el estudio en la vista)
        $subtotal = 0;
        foreach ($sessions as $session) {
            // Usamos asistencias registradas como base de cálculo
            $subtotal += $session->attendances->count();
        }

        // Pagos ya realizados a este profesor en este mes
        $payments = TeacherPayment::where('teacher_id', $teacherId)
            ->where('month_year', $monthYear)
            ->latest()
            ->get();

        return [
            'teacher'    => $teacher,
            'sessions'   => $sessions,
            'subtotal'   => $subtotal, // conteo de asistencias como base
            'month_year' => $monthYear,
            'payments'   => $payments,
        ];
    }

    /**
     * Calcula el total pagado hasta ahora en el mes.
     */
    public function getTotalPaid(int $teacherId, string $monthYear): int
    {
        return TeacherPayment::where('teacher_id', $teacherId)
            ->where('month_year', $monthYear)
            ->where('status', 'paid')
            ->sum('amount');
    }
}
