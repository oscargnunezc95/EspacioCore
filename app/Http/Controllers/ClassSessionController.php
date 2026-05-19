<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\Country;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Services\DocumentService;
use App\Rules\ValidDocument;

class ClassSessionController extends Controller
{
    public function show($subdomain, ClassSession $session)
    {
        // 0. SEGURIDAD: Obtenemos el estudio actual para evitar fuga de datos
        $studio = \App\Models\Studio::where('subdomain', $subdomain)->firstOrFail();

        // 1. Cargamos las relaciones con Eloquent
        $session->load(['attendances', 'payments']);
        
        // 2. Extraemos los IDs de pago usando la relación nativa
        $paidStudentIds = $session->payments->pluck('pivot.student_id')->toArray();

        // Regla de Seguridad (Self-Healing): Si alguien pagó, aseguramos su inscripción
        foreach ($paidStudentIds as $paidId) {
            $session->students()->syncWithoutDetaching([$paidId]);
            $session->attendances()->firstOrCreate(['student_id' => $paidId]);
        }
        
        // LA ÚNICA FUENTE DE VERDAD: Alumnas inscritas en esta sesión
        $students = $session->students()
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();

        $enrolledIds = $students->pluck('id')->toArray();

        // 3. CORRECCIÓN: Alumnas SOLO DEL ESTUDIO ACTUAL que aún no se inscriben
        $otherStudents = Student::where('studio_id', $studio->id)
            ->where('is_guest', false)
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc')
            ->get();
        
        // 4. CORRECCIÓN: Cargamos los países para el select del Modal
        $countries = Country::orderBy('name', 'asc')->get();
        
        $monthId = Carbon::parse($session->date)->format('Y-m');
            
        // 5. Profesores filtrados por el estudio seguro
        $teachers = Teacher::where('studio_id', $studio->id)->orderBy('first_name')->get();
        
        return view('classsessions.show', compact(
            'session', 
            'students', 
            'paidStudentIds', 
            'otherStudents', 
            'monthId', 
            'teachers', 
            'countries'
        ));
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
        $newMonthId = Carbon::parse($request->date)->format('Y-m');
        
        return redirect()->route('trainingmonth.show', [
            'subdomain' => $subdomain, 
            'month' => $newMonthId
        ])->with('success', 'Sesión específica actualizada correctamente.');
    }

    public function enrollStudent(Request $request, $subdomain, ClassSession $session)
    {
        $studio = \App\Models\Studio::where('subdomain', $subdomain)->firstOrFail();

        // 1. Obtenemos el código de país estrictamente desde la vista (Elegancia PHP 8)
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        // 2. ESTANDARIZAR EL DOCUMENTO ANTES DE VALIDAR
        // Esto es vital para que Rule::unique (que usa QueryBuilder) encuentre el RUT limpio.
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. Validación Blindada
        $validated = $request->validate([
            'enroll_mode'   => 'required|in:existing,new',
            'student_ids'   => 'nullable|required_if:enroll_mode,existing|array',
            'student_ids.*' => 'exists:students,id',
            'first_name'    => 'nullable|required_if:enroll_mode,new|string|max:255',
            'last_name'     => 'nullable|string|max:255',
            'email'         => 'nullable|required_if:enroll_mode,new|email',
            'country_id'    => 'nullable|required_if:enroll_mode,new|exists:countries,id',
            'national_id'   => [
                'nullable',
                'required_if:enroll_mode,new',
                'string',
                'max:50',
                new ValidDocument($countryCode),
                // Aseguramos que la alumna no exista ya en este estudio
                Rule::unique('students', 'national_id')->where(function ($query) use ($studio) {
                    return $query->where('studio_id', $studio->id);
                })
            ],
            'phone'         => 'nullable|string|max:20',
        ]);

        if ($request->enroll_mode === 'existing') {
            
            // 1. VINCULACIÓN A LA CLASE (La fuente de verdad de la vista)
            $session->students()->syncWithoutDetaching($request->student_ids);

            // 2. CREACIÓN DE ASISTENCIAS
            foreach ($request->student_ids as $studentId) {
                \App\Models\Attendance::firstOrCreate([
                    'class_session_id' => $session->id,
                    'student_id'       => $studentId,
                ]);
            }
            
            $mensaje = count($request->student_ids) > 1 
                        ? count($request->student_ids) . ' alumnas inscritas correctamente.' 
                        : 'Alumna inscrita correctamente.';

        } else {
            
            // CREACIÓN E INSCRIPCIÓN DE NUEVA ALUMNA
            // Ya no hacemos la limpieza manual aquí. El mutador en App\Models\Student hará su magia 
            // usando el request crudo (que ya limpiamos en el paso 2 con el merge).
            $student = Student::create([
                'studio_id'   => $studio->id,
                'first_name'  => $request->first_name,
                'last_name'   => $request->last_name,
                'name'        => trim($request->first_name . ' ' . $request->last_name),
                'email'       => $request->email,
                'country_id'  => $request->country_id,
                'national_id' => $request->national_id,
                'phone'       => $request->phone,
            ]);

            // 1. Vinculación a la clase
            $session->students()->syncWithoutDetaching([$student->id]);

            // 2. Creación de la asistencia
            \App\Models\Attendance::create([
                'class_session_id' => $session->id,
                'student_id'       => $student->id,
            ]);

            $mensaje = 'Nueva alumna creada e inscrita correctamente.';
        }

        return back()->with('success', $mensaje);
    }
}