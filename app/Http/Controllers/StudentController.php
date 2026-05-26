<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\Studio;
use App\Models\ClassSession;
use App\Models\Payment;
use Illuminate\Support\Facades\Config;
use App\Services\DocumentService;
use App\Rules\ValidDocument;
use App\Models\Country; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudentWelcomeMail;
use App\Mail\UserLinkedToStudioMail;
use App\Notifications\StudentAddedNotification;
use App\Services\TenantIdentityService; // 👈 NUEVO

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
            
        $countries = Country::orderBy('name', 'asc')->get();
        
        return view('students.index', compact('students', 'inactiveStudents', 'countries'));
    }

    public function store(Request $request, $subdomain, TenantIdentityService $identityService)
    {
        $studioId = Config::get('tenant.studio_id');
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 1. VERIFICACIÓN RÁPIDA: ¿Ya existe en este estudio? (Usando el Servicio)
        if ($identityService->isStudentInStudio($request->national_id, $studioId)) {
            // Lógica de rescate de papelera original
            $existingStudent = Student::withTrashed()
                ->where('studio_id', $studioId)
                ->where('national_id', $request->national_id)
                ->first();

            if ($existingStudent && $existingStudent->trashed()) {
                $existingStudent->restore();
                $existingStudent->update($request->except(['national_id']));
                return back()->with('success', 'La alumna/o estaba en la papelera y ha sido reactivada con sus nuevos datos.');
            }
            return back()->withErrors(['national_id' => 'Este documento ya pertenece a una alumna/o activa en tu estudio.'])->withInput();
        }

        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id', 
            'national_id' => [
                'nullable', 'string', 'max:255', new ValidDocument($countryCode),
                // Ya no validamos unique local aquí, el servicio lo hizo arriba.
            ],
            // 👇 REGLA DE ORO 1:1 INYECTADA 👇
            'email' => [
                'nullable', 'email', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existingUser = \App\Models\User::where('email', $value)->first();
                    // Si el correo existe pero le pertenece a un RUT distinto, bloqueamos.
                    if ($existingUser && $existingUser->national_id !== $request->national_id) {
                        $fail('Este correo ya está registrado con otro documento. ');
                    }
                }
            ],
            'phone'       => 'nullable|string|max:255'
        ]);

        // =========================================================
        // 2. MOTOR DE ONBOARDING VÍA SERVICIO
        // =========================================================
        $identity = $identityService->resolveGlobalUser(
            $request->national_id, 
            $request->email, 
            trim($request->first_name . ' ' . $request->last_name)
        );

        $user = $identity['user'];

        $studentData = $request->all();
        $studentData['studio_id'] = $studioId;
        $studentData['user_id'] = $user ? $user->id : null;

        $student = Student::create($studentData);

        // 3. DISPARO DE NOTIFICACIONES
        if ($user) {
            try {
                $studio = Studio::find($studioId);
                
                if ($identity['is_new']) {
                    Mail::to($user->email)->send(new StudentWelcomeMail($studio, $student, $identity['temp_password']));
                } else {
                    Mail::to($user->email)->send(new UserLinkedToStudioMail($studio, $user->name));
                }
                
                $user->notify(new StudentAddedNotification($studio));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Fallo onboarding alumna: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Alumna/o creada y notificada correctamente.');
    }

    public function update(Request $request, $subdomain, Student $student)
    {
        $studioId = Config::get('tenant.studio_id');
        
        // 1. OBTENER EL CÓDIGO DEL PAÍS (Elegancia PHP 8)
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        // 2. ESTANDARIZAR EL DOCUMENTO ANTES DE VALIDAR
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. VALIDACIÓN BLINDADA
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id',
            'national_id' => [
                'nullable',
                'string',
                'max:255',
                new ValidDocument($countryCode),
                Rule::unique('students', 'national_id')
                    ->ignore($student->id)
                    ->where(function ($query) use ($studioId) {
                        return $query->where('studio_id', $studioId);
                    })
            ],
            // 👇 REGLA DE ORO 1:1 INYECTADA 👇
            'email' => [
                'nullable', 'email', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existingUser = \App\Models\User::where('email', $value)->first();
                    if ($existingUser && $existingUser->national_id !== $request->national_id) {
                        $fail('Este correo ya está registrado con otro documento. Si es un apoderado inscribiendo a otra persona, añade "+nombre" antes del @ (ej: correo+hijo@gmail.com).');
                    }
                }
            ],
            'phone'       => 'nullable|string|max:255'
        ]);

        $student->update($request->all());
        
        // Sincronización silenciosa opcional: si el estudiante tiene un user_id, 
        // puedes actualizar el correo en la tabla global para que no queden desfasados
        if ($student->user_id && $request->filled('email')) {
            \App\Models\User::where('id', $student->user_id)->update(['email' => $request->email]);
        }
        
        return back()->with('success', 'Datos actualizados.');
    }

    public function destroy($subdomain, Student $student)
    {
        $student->delete();
        return back()->with('success', 'Alumna/o desactivada. Podrás encontrarla en la pestaña de Inactivas.');
    }

    public function restore($subdomain, $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();
        return back()->with('success', 'Alumna/o reactivada correctamente.');
    }

    public function forceDelete($subdomain, $id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->forceDelete();
        return back()->with('success', 'Alumna/o eliminada permanentemente del sistema.');
    }

    public function calendar($subdomain, $studentId, $month = null)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $student = Student::withTrashed()->findOrFail($studentId);
        $monthDate = $month ? Carbon::createFromFormat('Y-m', $month) : Carbon::now();

        // 1. Cargamos las relaciones completas para la grilla del calendario
        $sessions = ClassSession::with(['workshop.studio', 'students', 'attendances'])
            ->where('studio_id', $studio->id)
            ->whereYear('date', $monthDate->year)
            ->whereMonth('date', $monthDate->month)
            ->orderBy('start_time', 'asc')
            ->get();

        $sessionsByDate = $sessions->groupBy('date');

        // 2. Consulta Eloquent limpia para extraer los IDs pagados sin usar DB::table()
        $paidSessionIds = Payment::where('student_id', $student->id)
            ->with('classSessions')
            ->get()
            ->flatMap(function($payment) {
                return $payment->classSessions->pluck('id');
            })
            ->unique()
            ->toArray();

        return view('students.calendar', compact('student', 'monthDate', 'sessionsByDate', 'paidSessionIds'));
    }
}