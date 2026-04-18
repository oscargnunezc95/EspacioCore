<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function toggle(ClassSession $session, Student $student)
    {
        $attendance = Attendance::where('class_session_id', $session->id)
                                ->where('student_id', $student->id)
                                ->first();

        // Si ya tenía la asistencia, se la quitamos
        if ($attendance) {
            $attendance->delete();
            $present = false;
        } else {
            // Si no la tenía, la marcamos como presente
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