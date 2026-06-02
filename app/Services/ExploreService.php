<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ExploreService
{
    /**
     * Versión para colecciones simples (no paginadas).
     * Útil para el portal del alumno y otras vistas sin paginación.
     *
     * @param  \Illuminate\Support\Collection  $sessions
     * @return \Illuminate\Support\Collection
     */
    public function enrichSessionCollection($sessions)
    {
        if ($sessions->isEmpty()) {
            return $sessions;
        }

        $sessionIds = $sessions->pluck('id')->toArray();

        // 1. Agregados: paid_count y pending_count
        $stats = DB::table('class_session_student')
            ->select('class_session_id')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->whereIn('class_session_id', $sessionIds)
            ->groupBy('class_session_id')
            ->get()
            ->keyBy('class_session_id');

        // 2. Aplicar a cada sesión (usa el accessor $session->max_students que lee la relación schedule)
        foreach ($sessions as $session) {
            $stat = $stats->get($session->id);
            $maxStudents = $session->max_students; // Accessor: schedule->max_students ?? 99

            $paidCount   = $stat->paid_count ?? 0;
            $pendingCount = $stat->pending_count ?? 0;
            $available   = max(0, $maxStudents - $paidCount);

            $session->paid_count      = $paidCount;
            $session->pending_count   = $pendingCount;
            $session->available_spots = $available;
            $session->max_spots       = $maxStudents;
        }

        return $sessions;
    }

    /**
     * Enriquece una colección paginada de ClassSessions con:
     *  - paid_count:     alumnos que ya pagaron (ocupan cupo real)
     *  - pending_count:  alumnos interesados (reservaron, sin pagar)
     *  - available_spots: cupos que quedan (schedule.max_students - paid_count)
     *  - pending_students: lista de alumnos en espera (solo IDs + nombres, para la cola visual)
     *
     * @param  LengthAwarePaginator  $sessions
     * @return LengthAwarePaginator  (la misma instancia, mutada)
     */
    public function enrichSessionStats(LengthAwarePaginator $sessions): LengthAwarePaginator
    {
        if ($sessions->isEmpty()) {
            return $sessions;
        }

        $sessionIds = $sessions->pluck('id')->toArray();

        // 1. Agregados: cuántos pagados y cuántos pendientes por sesión
        // ESTO SE MANTIENE PORQUE ES ULTRA RÁPIDO (Solo trae números)
        $stats = DB::table('class_session_student')
            ->select('class_session_id')
            ->selectRaw("SUM(CASE WHEN payment_status = 'paid' THEN 1 ELSE 0 END) as paid_count")
            ->selectRaw("SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_count")
            ->whereIn('class_session_id', $sessionIds)
            ->groupBy('class_session_id')
            ->get()
            ->keyBy('class_session_id');

        // ¡EL PASO 2 QUE HACÍA EL JOIN CON LA TABLA STUDENTS FUE ELIMINADO COMPLETAMENTE!

        // 2. Mutar cada session del paginador (usa el accessor $session->max_students)
        foreach ($sessions as $session) {
            $stat = $stats->get($session->id);
            $maxStudents = $session->max_students; // Accessor: schedule->max_students ?? 99

            $paidCount   = $stat->paid_count ?? 0;
            $pendingCount = $stat->pending_count ?? 0;
            $available   = max(0, $maxStudents - $paidCount);

            $session->paid_count      = $paidCount;
            $session->pending_count   = $pendingCount;
            $session->available_spots = $available;
            $session->max_spots       = $maxStudents;
            // La línea de $session->pending_students también fue eliminada
        }

        return $sessions;
    }
}
