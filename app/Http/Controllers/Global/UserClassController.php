<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Workshop;
use App\Models\ClassSession;
use Carbon\Carbon;

class UserClassController extends Controller
{
    public function asStudent()
    {
        $studentProfileIds = Student::where('user_id', Auth::id())->pluck('id');

        $workshops = Workshop::with(['studio', 'teacher'])
            ->whereHas('classSessions.students', function ($query) use ($studentProfileIds) {
                $query->whereIn('students.id', $studentProfileIds);
            })
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('global.classes.student', compact('workshops'));
    }

    public function asTeacher()
    {
        $teacherProfileIds = Teacher::where('user_id', Auth::id())->pluck('id');

        // Buscamos todas las SESIONES (no solo talleres) asignadas a este profesor
        $sessions = ClassSession::with(['workshop.studio'])
            ->whereHas('workshop', function($q) use ($teacherProfileIds) {
                $q->whereIn('teacher_id', $teacherProfileIds);
            })
            // Filtramos desde el mes actual en adelante para no llenar de tarjetas viejas
            ->where('date', '>=', Carbon::now()->startOfMonth())
            ->orderBy('date', 'asc')
            ->get();

        // Agrupamos las sesiones por Año-Mes (Ej: "2026-05")
        $months = $sessions->groupBy(function($session) {
            return Carbon::parse($session->date)->format('Y-m');
        })->map(function($monthSessions, $monthKey) {
            return [
                'id' => $monthKey,
                'name' => ucfirst(Carbon::parse($monthKey . '-01')->translatedFormat('F Y')), // Ej: "Mayo 2026"
                'session_count' => $monthSessions->count(),
                // Sacamos los nombres de los estudios donde hace clases ese mes
                'studios' => $monthSessions->pluck('workshop.studio.name')->unique()->implode(', ')
            ];
        });
        
        return view('global.classes.teacher', compact('months'));
    }

    public function teacherCalendar($month)
    {
        $teacherProfileIds = Teacher::where('user_id', Auth::id())->pluck('id');
        $parsedMonth = Carbon::parse($month . '-01');

        // Traemos las sesiones de ese mes exacto
        $sessions = ClassSession::with(['workshop.studio', 'workshop.discipline.area'])
            ->whereHas('workshop', function($q) use ($teacherProfileIds) {
                $q->whereIn('teacher_id', $teacherProfileIds);
            })
            ->whereMonth('date', $parsedMonth->month)
            ->whereYear('date', $parsedMonth->year)
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get()
            ->groupBy('date'); // Agrupamos por día exacto para armar la agenda

        return view('global.classes.teacher_calendar', compact('sessions', 'parsedMonth', 'month'));
    }
    public function teacherSession(ClassSession $session)
    {
        // Validar que el usuario autenticado sea realmente el profesor de este taller (Seguridad)
        $teacherProfileIds = Teacher::where('user_id', Auth::id())->pluck('id')->toArray();
        if (!in_array($session->workshop->teacher_id, $teacherProfileIds)) {
            abort(403, 'No tienes permiso para ver esta clase.');
        }

        $session->load('attendances', 'workshop.studio');
        
        $paidStudentIds = \Illuminate\Support\Facades\DB::table('class_session_payment')
            ->where('class_session_id', $session->id)
            ->pluck('student_id')
            ->toArray();

        foreach ($paidStudentIds as $paidId) {
            $session->students()->syncWithoutDetaching([$paidId]);
            $session->attendances()->firstOrCreate(['student_id' => $paidId]);
        }
        
        $students = $session->students()
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();

        $parsedMonth = Carbon::parse($session->date);

        return view('global.classes.teacher_session', compact('session', 'students', 'paidStudentIds', 'parsedMonth'));
    }
}