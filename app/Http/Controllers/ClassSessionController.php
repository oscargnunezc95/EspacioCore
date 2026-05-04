<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ClassSessionController extends Controller
{
    public function show($subdomain, ClassSession $session)
    {
        $session->load('attendances');
        
        // Obtenemos los IDs de quienes ya pagaron esta sesión
        $paidStudentIds = DB::table('class_session_payment')
            ->where('class_session_id', $session->id)
            ->pluck('student_id')
            ->toArray();

        // Regla de Seguridad: Si alguien pagó, debemos asegurar que esté en la lista de inscritas y presente.
        foreach ($paidStudentIds as $paidId) {
            $session->students()->syncWithoutDetaching([$paidId]);
            $session->attendances()->firstOrCreate(['student_id' => $paidId]);
        }
        
        // LA ÚNICA FUENTE DE VERDAD: Alumnas inscritas en esta sesión específica
        $students = $session->students()
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();

        $enrolledIds = $students->pluck('id')->toArray();

        // Alumnas del estudio que AÚN NO se inscriben en esta clase (Para el Modal)
        $otherStudents = Student::where('is_guest', false)
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();
        
        $monthId = Carbon::parse($session->date)->format('Y-m');
        
        return view('sessions.show', compact('session', 'students', 'otherStudents', 'paidStudentIds', 'monthId'));
    }

    public function cancel($subdomain, ClassSession $session)
    {
        $session->update(['is_cancelled' => !$session->is_cancelled]);
        $status = $session->is_cancelled ? 'cancelada' : 'restaurada';
        return back()->with('success', "Clase $status correctamente.");
    }

    // Renombramos de storeInfrequent a enrollStudent
    public function enrollStudent(Request $request, $subdomain, ClassSession $session)
    {
        $request->validate([
            'enroll_mode' => 'required|in:existing,new',
            'student_id' => 'required_if:enroll_mode,existing|nullable|exists:students,id',
            'first_name' => 'required_if:enroll_mode,new|nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'required_if:enroll_mode,new',
                'nullable',
                'email',
                Rule::unique('students', 'email')->where(function ($query) {
                    return $query->where('studio_id', session('current_studio_id'));
                })
            ]
        ], [
            'student_id.required_if' => 'Debes seleccionar una alumna de la lista.',
            'first_name.required_if' => 'Debes ingresar el nombre de la alumna.',
            'email.required_if' => 'Debes ingresar un correo para crear la ficha.',
            'email.unique' => 'Este correo ya está registrado en tu estudio. Búscala en la pestaña "Buscar en Estudio".'
        ]);

        if ($request->enroll_mode === 'existing') {
            $student = Student::findOrFail($request->student_id);
        } else {
            // Crea la alumna a nivel de Estudio
            $student = Student::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'is_guest' => false
            ]);
        }

        // 1. La Inscribimos en la clase
        $session->students()->syncWithoutDetaching([$student->id]);
        
        // 2. La marcamos como presente (Asumiendo que si la profe la agrega a mano en el momento, es porque llegó)
        $session->attendances()->firstOrCreate(['student_id' => $student->id]);

        return back()->with('success', 'Alumna inscrita en la clase y marcada como presente.');
    }
}