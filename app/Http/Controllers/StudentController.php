<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function index($subdomain)
    {
        $students = Student::where('is_guest', false)
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();
            
        $inactiveStudents = Student::onlyTrashed()
            ->where('is_guest', false)
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();
        
        return view('students.index', compact('students', 'inactiveStudents'));
    }

    public function store(Request $request, $subdomain)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                // El correo debe ser único dentro de este estudio específico
                Rule::unique('students', 'email')->where(function ($query) {
                    return $query->where('studio_id', session('current_studio_id'));
                })
            ],
            'phone' => 'nullable|string'
        ]);

        Student::create($request->all());
        return back()->with('success', 'alumna/ocreada correctamente.');
    }

    public function update(Request $request, $subdomain, Student $student)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                // Ignoramos el ID de esta alumna/oal validar la unicidad del correo
                Rule::unique('students', 'email')
                    ->ignore($student->id)
                    ->where(function ($query) {
                        return $query->where('studio_id', session('current_studio_id'));
                    })
            ],
            'phone' => 'nullable|string'
        ]);

        $student->update($request->all());
        return back()->with('success', 'Datos actualizados.');
    }

    public function destroy($subdomain, Student $student)
    {
        $student->delete();
        return back()->with('success', 'alumna/odesactivada. Podrás encontrarla en la pestaña de Inactivas.');
    }

    public function restore($subdomain, $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();
        return back()->with('success', 'alumna/oreactivada correctamente.');
    }

    public function forceDelete($subdomain, $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->forceDelete();
        return back()->with('success', 'alumna/oeliminada permanentemente del sistema.');
    }

    public function calendar($subdomain, $studentId, $month = null)
    {
        $student = Student::withTrashed()->findOrFail($studentId);
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        $allAttendances = Attendance::with('classSession.workshop')
            ->where('student_id', $student->id)
            ->get()
            ->sortByDesc(function ($att) {
                return $att->classSession->date . ' ' . $att->classSession->start_time;
            });

        $paidSessionIds = DB::table('class_session_payment')
            ->where('student_id', $student->id)
            ->pluck('class_session_id')
            ->toArray();

        $unpaidIds = [];
        foreach ($allAttendances as $att) {
            if (!in_array($att->class_session_id, $paidSessionIds)) {
                $unpaidIds[] = $att->id;
            }
        }

        $monthlyAttendances = clone $allAttendances;
        $monthlyAttendances = $monthlyAttendances->filter(function($att) use ($monthDate) {
            $date = Carbon::parse($att->classSession->date);
            return $date->year == $monthDate->year && $date->month == $monthDate->month;
        });

        $attendancesByDate = $monthlyAttendances->groupBy(function($att) {
            return $att->classSession->date;
        });

        return view('students.calendar', compact('student', 'monthDate', 'attendancesByDate', 'unpaidIds'));
    }
}