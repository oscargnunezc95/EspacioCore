<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SessionController extends Controller
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
            
        // Sacamos el studio_id directamente de la relación de la sesión o del taller
        $teachers = Teacher::where('studio_id', $session->studio_id ?? $session->workshop->studio_id)->orderBy('first_name')->get();
        
        return view('sessions.show', compact('session', 'students', 'paidStudentIds', 'otherStudents', 'monthId', 'teachers'));
    }

    public function update(Request $request, $subdomain, ClassSession $session)
    {
        // 1. Validamos todos los campos posibles de edición
        $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'teacher_id' => 'nullable|exists:teachers,id',
            'is_cancelled' => 'boolean'
        ]);

        // 2. Aplicamos los cambios a la sesión específica
        $session->update([
            'date' => $request->date,
            'start_time' => $request->start_time,
            'teacher_id' => $request->teacher_id, 
            'is_cancelled' => $request->boolean('is_cancelled') 
        ]);

        // 3. Redirigimos de vuelta. 
        // OJO: Como la fecha cambió, si rediriges a la URL anterior (que dependía del mes viejo) podría haber un salto extraño.
        // Lo más seguro es redirigir al calendario del mes al que pertenece la NUEVA fecha.
        // 3. Redirigimos de vuelta a trainingmonth.show
        $newMonthId = \Carbon\Carbon::parse($request->date)->format('Y-m');
        
        return redirect()->route('trainingmonth.show', [
            'subdomain' => $subdomain, 
            'month' => $newMonthId
        ])->with('success', 'Sesión específica actualizada correctamente.');
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