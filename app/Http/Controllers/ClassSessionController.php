<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Teacher;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
// ELIMINADO: use Illuminate\Support\Facades\DB;

class ClassSessionController extends Controller
{
    public function show($subdomain, ClassSession $session)
    {
        // 1. Cargamos las relaciones con Eloquent
        $session->load(['attendances', 'payments']);
        
        // 2. Extraemos los IDs de pago usando la relación nativa (Adiós DB::table)
        $paidStudentIds = $session->payments->pluck('pivot.student_id')->toArray();

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
        
        return view('classsessions.show', compact('session', 'students', 'paidStudentIds', 'otherStudents', 'monthId', 'teachers'));
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

        // 3. Redirigimos al calendario del mes al que pertenece la NUEVA fecha.
        $newMonthId = \Carbon\Carbon::parse($request->date)->format('Y-m');
        
        return redirect()->route('trainingmonth.show', [
            'subdomain' => $subdomain, 
            'month' => $newMonthId
        ])->with('success', 'Sesión específica actualizada correctamente.');
    }

    public function enrollStudent(Request $request, $subdomain, ClassSession $session)
    {
        // Obtenemos el ID del estudio de forma 100% segura
        $studioId = $session->studio_id ?? $session->workshop->studio_id;

        $request->validate([
            'enroll_mode' => 'required|in:existing,new',
            'student_id' => 'required_if:enroll_mode,existing|nullable|exists:students,id',
            'first_name' => 'required_if:enroll_mode,new|nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => [
                'required_if:enroll_mode,new',
                'nullable',
                'email',
                // Validación Segura del Email por Estudio
                Rule::unique('students', 'email')->where(function ($query) use ($studioId) {
                    return $query->where('studio_id', $studioId);
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
            // CORRECCIÓN CRÍTICA: Asignar la alumna al estudio
            $student = Student::create([
                'studio_id'  => $studioId, // <-- SIN ESTO, LA ALUMNA QUEDA HUÉRFANA
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'is_guest'   => false
            ]);
        }

        // 1. La Inscribimos en la clase
        $session->students()->syncWithoutDetaching([$student->id]);
        
        // 2. La marcamos como presente a través de la relación limpia
        $session->attendances()->firstOrCreate(['student_id' => $student->id]);

        return back()->with('success', 'Alumna inscrita en la clase y marcada como presente.');
    }
}