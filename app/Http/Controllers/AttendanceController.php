<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    // Agregamos $subdomain como primer parámetro para respetar la ruta
    public function toggle($subdomain, ClassSession $session, Student $student)
    {
        // El Global Scope ya protege esta consulta. 
        // Solo buscará asistencias que pertenezcan al estudio actual.
        $attendance = Attendance::where('class_session_id', $session->id)
                                ->where('student_id', $student->id)
                                ->first();

        // Si ya tenía la asistencia, se la quitamos
        if ($attendance) {
            $attendance->delete();
            $present = false;
        } else {
            // Si no la tenía, la marcamos como presente.
            // Magia pura: No necesitamos pasar el 'studio_id', 
            // el trait BelongsToStudio lo inyecta automáticamente por debajo.
            Attendance::create([
                'class_session_id' => $session->id,
                'student_id' => $student->id
            ]);
            $present = true;
        }

        // Devolvemos la respuesta al JavaScript del botón para que cambie de color
        return response()->json(['present' => $present]);
    }
}