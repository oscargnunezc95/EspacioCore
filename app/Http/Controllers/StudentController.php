<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Student;
use App\Models\User;
use App\Models\Studio;
use App\Models\UserDependent;
use App\Models\ClassSession;
use App\Models\Payment;
use Illuminate\Support\Facades\Config;
use App\Services\DocumentService;
use App\Rules\ValidDocument;
use App\Models\Country; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserLinkedToStudioMail;
use App\Notifications\StudentAddedNotification;

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

    public function store(Request $request, $subdomain)
    {
        $studioId = Config::get('tenant.studio_id');
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 1. VERIFICACIÓN RÁPIDA: ¿Ya existe en este estudio?
        if ($request->filled('national_id') && Student::withoutGlobalScopes()
                ->where('national_id', $request->national_id)
                ->where('studio_id', $studioId)
                ->exists()) {
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
                'required', 'string', 'max:255', new ValidDocument($countryCode),
            ],
            // 👇 REGLA DE ORO 1:1
            'email' => [
                'nullable', 'email', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existingUser = User::where('email', $value)->first();
                    if ($existingUser && $existingUser->national_id !== $request->national_id) {
                        // Excepción: ¿el dueño del correo es apoderado de este national_id?
                        $isGuardian = UserDependent::where('user_id', $existingUser->id)
                            ->where('national_id', $request->national_id)
                            ->exists();
                        if (! $isGuardian) {
                            $fail('Este correo ya está registrado con otro documento. ');
                        }
                    }
                }
            ],
            'phone'       => 'nullable|string|max:255'
        ]);

        // 2. BÚSQUEDA DE USUARIO GLOBAL (SIN CREAR)
        // Solo por national_id + country_id — el email no se usa para vincular
        $user = User::where('national_id', $request->national_id)
            ->where('country_id', $request->country_id)
            ->first();

        $studentData = $request->all();
        $studentData['studio_id'] = $studioId;
        $studentData['user_id'] = $user ? $user->id : null;

        $student = Student::create($studentData);

        // 3. DISPARO DE NOTIFICACIONES
        $studio = Studio::find($studioId);

        if ($user) {
            // Vinculado a un usuario existente
            try {
                Mail::to($user->email)->send(new UserLinkedToStudioMail($studio, $user->name));
                $user->notify(new StudentAddedNotification($studio, $student));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Fallo notificación vinculación alumna: ' . $e->getMessage());
            }
        } elseif ($request->filled('email')) {
            // Huérfano con email → invitación a registrarse
            try {
                Mail::to($request->email)->queue(new \App\Mail\StudentInvitationMail($studio, $student));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Fallo envío invitación alumna: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Alumna/o creada correctamente.');
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
                'required',
                'string',
                'max:255',
                new ValidDocument($countryCode),
                Rule::unique('students', 'national_id')
                    ->ignore($student->id)
                    ->where(function ($query) use ($studioId, $request) {
                        return $query->where('studio_id', $studioId)
                                     ->where('country_id', $request->country_id);
                    })
            ],
            // 👇 REGLA DE ORO 1:1
            'email' => [
                'nullable', 'email', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existingUser = User::where('email', $value)->first();
                    if ($existingUser && $existingUser->national_id !== $request->national_id) {
                        // Excepción: ¿el dueño del correo es apoderado de este national_id?
                        $isGuardian = UserDependent::where('user_id', $existingUser->id)
                            ->where('national_id', $request->national_id)
                            ->exists();
                        if (! $isGuardian) {
                            $fail('Este correo ya está registrado con otro documento. Si es un apoderado inscribiendo a otra persona, añade \"+nombre\" antes del @ (ej: correo+hijo@gmail.com).');
                        }
                    }
                }
            ],
            'phone'       => 'nullable|string|max:255'
        ]);

        $student->update($request->all());
        
        // Sincronización silenciosa opcional: si el estudiante tiene un user_id, 
        // puedes actualizar el correo en la tabla global para que no queden desfasados
        if ($student->user_id && $request->filled('email')) {
            User::where('id', $student->user_id)->update(['email' => $request->email]);
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
        // Excluimos pagos con estado "refunded" o "refunded_overbooking" porque esas
        // clases deben mostrarse como "Disponible" para que puedan volver a pagarse.
        $paidSessionIds = Payment::where('student_id', $student->id)
            ->whereNotIn('status', ['refunded', 'refunded_overbooking'])
            ->with('classSessions')
            ->get()
            ->flatMap(function($payment) {
                return $payment->classSessions->pluck('id');
            })
            ->unique()
            ->toArray();

        return view('students.calendar', compact('student', 'monthDate', 'sessionsByDate', 'paidSessionIds', 'studio'));
    }

    public function payments($subdomain, $studentId)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $student = Student::withTrashed()->findOrFail($studentId);

        $payments = $student->payments()->latest()->with('classSessions.workshop')->paginate(15);

        return view('students.payments', compact('student', 'payments'));
    }
}