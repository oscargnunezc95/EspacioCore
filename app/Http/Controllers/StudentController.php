<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Attendance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StudentController extends Controller
{
    public function index()
    {
        // Limpiamos el directorio de los "Fantasmas"
        $students = Student::where('is_guest', false)->orderBy('name', 'asc')->get();
        $inactiveStudents = Student::onlyTrashed()->where('is_guest', false)->orderBy('name', 'asc')->get();
        
        return view('students.index', compact('students', 'inactiveStudents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'rut' => 'required|string|unique:students,rut',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string'
        ]);

        Student::create($request->all());
        return back()->with('success', 'Alumna creada correctamente.');
    }

    public function update(Request $request, Student $student)
    {
        $request->validate([
            'rut' => 'required|string|unique:students,rut,' . $student->id,
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string'
        ]);

        $student->update($request->all());
        return back()->with('success', 'Datos actualizados.');
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return back()->with('success', 'Alumna desactivada. Podrás encontrarla en la pestaña de Inactivas.');
    }

    public function restore($id)
    {
        Student::withTrashed()->find($id)->restore();
        return back()->with('success', 'Alumna reactivada correctamente.');
    }

    public function forceDelete($id)
    {
        Student::withTrashed()->find($id)->forceDelete();
        return back()->with('success', 'Alumna eliminada permanentemente del sistema.');
    }

    // CALENDARIO DE ASISTENCIAS: Ahora funciona con Pagos por Fechas
    public function calendar($studentId, $month = null) // Cambiamos Student por $studentId
    {
        // Buscamos a la alumna incluyendo las deshabilitadas (fantasmas/no frecuentes)
        $student = Student::withTrashed()->findOrFail($studentId);

        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        $allAttendances = Attendance::with('classSession.workshop')
            ->where('student_id', $student->id)
            ->get()
            ->sortByDesc(function ($att) {
                return $att->classSession->date . ' ' . $att->classSession->start_time;
            });

        // 1. Buscamos todas las clases que esta alumna YA PAGÓ
        $paidSessionIds = DB::table('class_session_payment')
            ->where('student_id', $student->id)
            ->pluck('class_session_id')
            ->toArray();

        // 2. Comparamos sus asistencias. Si asistió a una clase que NO está pagada, la marcamos de rojo.
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