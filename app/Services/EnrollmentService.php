<?php

namespace App\Services;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\User;
use App\Notifications\ClassFullNotification;
use App\Notifications\SpotReservedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EnrollmentService
{
    /**
     * Retorna información de capacidad para una o varias sesiones.
     *
     * Fundamento: Método compartido por las 4 capas anti-overbooking para
     * garantizar que el cálculo de cupos sea consistente en todo el sistema.
     *
     * @param int|int[] $sessionIds  Una o varias IDs de ClassSession
     * @return array  Keyed by session_id: ['max_students' => int, 'paid_count' => int, 'available_spots' => int]
     */
    public function getCapacityInfo($sessionIds): array
    {
        $ids = is_array($sessionIds) ? $sessionIds : [$sessionIds];
        if (empty($ids)) return [];

        // 1. Cargar modelos para obtener max_students (jerarquía: schedule → workshop → 99)
        $sessions = ClassSession::withoutGlobalScopes()
            ->with(['schedule', 'workshop'])
            ->whereIn('id', $ids)
            ->get()
            ->keyBy('id');

        // 2. Consulta agrupada: paid_count por sesión
        $paidCounts = DB::table('class_session_student')
            ->whereIn('class_session_id', $ids)
            ->where('payment_status', 'paid')
            ->groupBy('class_session_id')
            ->selectRaw('class_session_id, COUNT(*) as paid_count')
            ->pluck('paid_count', 'class_session_id');

        // 3. Armar resultado
        $result = [];
        foreach ($ids as $id) {
            $session = $sessions->get($id);
            $maxStudents = $session ? $session->max_students : 99;
            $paidCount = (int) ($paidCounts->get($id) ?? 0);
            $result[$id] = [
                'max_students'    => $maxStudents,
                'paid_count'      => $paidCount,
                'available_spots' => max(0, $maxStudents - $paidCount),
            ];
        }

        return $result;
    }

    /**
     * Maneja el attach/detach en la tabla pivote class_session_student.
     *
     * - 'add':     Inserta al estudiante con payment_status='pending' si no está ya.
     * - 'remove':  Elimina al estudiante si existe y no ha pagado.
     *
     * @param ClassSession $session
     * @param Student      $student
     * @param string       $action   'add' | 'remove'
     * @return string                'enrolled' | 'removed' | 'unchanged'
     */
    public function toggleSpot(ClassSession $session, Student $student, string $action): string
    {
        $existing = $session->students()
            ->withoutGlobalScopes()
            ->where('students.id', $student->id)
            ->first();

        if ($action === 'remove') {
            if ($existing && $existing->pivot->payment_status !== 'paid') {
                $session->students()->withoutGlobalScopes()->detach($student->id);
                return 'removed';
            }
            return 'unchanged';
        }

        // action === 'add'
        if (!$existing) {
            $session->students()->withoutGlobalScopes()->attach(
                $student->id,
                ['payment_status' => 'pending']
            );
            return 'enrolled';
        }

        return 'enrolled'; // Ya estaba inscrito
    }

    /**
     * Recalcula cupos disponibles y notifica a los estudiantes pendientes.
     *
     * - Si la clase está llena  → ClassFullNotification
     * - Si aún hay cupos       → SpotReservedNotification
     * - EXCEPCIÓN CRÍTICA: excluye al usuario con $excludeUserId para evitar
     *   el "eco de transmisión" (que el usuario que acaba de pagar/reservar
     *   reciba su propia notificación).
     *
     * @param int[] $sessionIds     IDs de sesiones afectadas
     * @param int   $excludeUserId  User a excluir de las notificaciones
     * @return void
     */
    public function notifyCapacityChange(array $sessionIds, int $excludeUserId): void
    {
        foreach ($sessionIds as $sessionId) {
            try {
                $session = ClassSession::withoutGlobalScopes()
                    ->with(['workshop' => fn($q) => $q->withoutGlobalScopes(), 'schedule'])
                    ->find($sessionId);

                if (!$session) continue;

                $maxStudents = $session->max_students;

                $paidCount = \Illuminate\Support\Facades\DB::table('class_session_student')
                    ->where('class_session_id', $sessionId)
                    ->where('payment_status', 'paid')
                    ->count();

                $availableSpots = max(0, $maxStudents - $paidCount);

                // 1. Obtenemos los IDs de los estudiantes que están pendientes (En el carrito)
                $pendingStudentIds = \Illuminate\Support\Facades\DB::table('class_session_student')
                    ->where('class_session_id', $sessionId)
                    ->where('payment_status', 'pending')
                    ->pluck('student_id');

                if ($pendingStudentIds->isEmpty()) continue;

                // 2. Obtenemos los usuarios a notificar, EXCLUYENDO al causante del cambio
                $pendingUsers = \App\Models\User::whereHas('studentProfiles', function ($q) use ($pendingStudentIds) {
                        $q->withoutGlobalScopes()->whereIn('id', $pendingStudentIds);
                    })
                    ->where('id', '!=', $excludeUserId) // EXCEPCIÓN CRÍTICA
                    ->get();

                if ($availableSpots <= 0) {
                    
                    // =========================================================================
                    // REGLA DE ORO: CLASE LLENA -> VACIAR CARRITOS AUTOMÁTICAMENTE
                    // =========================================================================
                    \Illuminate\Support\Facades\DB::table('class_session_student')
                        ->where('class_session_id', $sessionId)
                        ->where('payment_status', 'pending')
                        ->delete();

                    // 3. Despachar notificaciones de "Clase Llena" a los afectados
                    foreach ($pendingUsers as $pendingUser) {
                        try {
                            $pendingUser->notify(new \App\Notifications\ClassFullNotification($session));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error enviando ClassFullNotification: ' . $e->getMessage());
                        }
                    }
                } else {
                    // 4. Despachar notificaciones de "Cupos Bajando" si aún hay espacio
                    foreach ($pendingUsers as $pendingUser) {
                        try {
                            $pendingUser->notify(new \App\Notifications\SpotReservedNotification($session, $availableSpots));
                        } catch (\Exception $e) {
                            \Illuminate\Support\Facades\Log::error('Error enviando SpotReservedNotification: ' . $e->getMessage());
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Error en notifyCapacityChange para sesión ' . $sessionId . ': ' . $e->getMessage());
            }
        }
    }
}
