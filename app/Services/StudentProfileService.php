<?php

namespace App\Services;

use App\Models\Student;
use App\Models\Studio;
use App\Models\User;
use App\Notifications\StudentAddedNotification;

class StudentProfileService
{
    /**
     * Busca o crea la ficha de alumno (Student) para el asistente.
     *
     * - Busca por national_id + studio_id.
     * - Si no hay documento, busca por user_id + first_name como fallback.
     * - Si existe y está huérfana (user_id = null) pero el documento coincide
     *   con el authUser, la reclama (setea user_id y email).
     * - Si no existe, la crea y dispara StudentAddedNotification.
     * - REGLA DE CORREO: $student->email SIEMPRE es exactamente $authUser->email,
     *   sin subaddressing, tanto para titular como para familiar.
     *
     * @param array $attendeeData  [first_name, last_name?, national_id, country_id?]
     * @param int   $studioId
     * @param User  $authUser
     * @return Student
     */
    public function findOrCreateAttendee(array $attendeeData, int $studioId, User $authUser): Student
    {
        $query = Student::withoutGlobalScopes()->where('studio_id', $studioId);

        if (!empty($attendeeData['national_id'])) {
            $query->where('national_id', $attendeeData['national_id']);
        } else {
            $query->where('user_id', $authUser->id)
                  ->whereNull('national_id')
                  ->where('first_name', $attendeeData['first_name']);
        }

        $student = $query->first();

        if ($student) {
            // Si la ficha está huérfana (user_id = null) y el documento coincide
            // con el authUser, reclamarla actualizando user_id y email.
            if (empty($student->user_id)) {
                $isSelf = ($attendeeData['national_id'] === $authUser->national_id);
                if ($isSelf) {
                    $student->update([
                        'user_id' => $authUser->id,
                        'email'   => $authUser->email,
                    ]);
                }
            }
            return $student;
        }

        // ─── Crear nueva ficha ────────────────────────────────────────
        $isSelf = ($attendeeData['national_id'] === $authUser->national_id);

        $student = new Student();
        $student->user_id     = $isSelf ? $authUser->id : null;
        $student->studio_id   = $studioId;
        $student->first_name  = $attendeeData['first_name'];
        $student->last_name   = $attendeeData['last_name'] ?? null;
        $student->email       = $authUser->email; // REGLA: siempre el email del authUser
        $student->national_id = $attendeeData['national_id'] ?? null;
        $student->country_id  = $attendeeData['country_id'] ?? $authUser->country_id;
        $student->is_guest    = false;
        $student->save();

        // Disparar notificación de nueva ficha creada
        try {
            $studio = Studio::find($studioId);
            $authUser->notify(new StudentAddedNotification($studio, $student));
        } catch (\Exception $e) {
            // Notificación fallida no debe romper el flujo
        }

        return $student;
    }
}
