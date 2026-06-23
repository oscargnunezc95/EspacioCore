<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\Teacher;
use App\Models\User;
use App\Models\Studio;
use App\Models\Country;
use App\Services\DocumentService;
use App\Rules\ValidDocument;
use App\Mail\TeacherInvitationMail;
use App\Mail\UserLinkedAsTeacherMail;
use App\Notifications\TeacherAddedNotification;

class TeacherController extends Controller
{
    public function index($subdomain)
    {
        $teachers = Teacher::orderBy('first_name', 'asc')->get();
        $inactiveTeachers = Teacher::onlyTrashed()->orderBy('first_name', 'asc')->get();
        
        // Enviamos los países a la vista para el Modal de Profesores
        $countries = Country::orderBy('name', 'asc')->get();
        
        return view('teachers.index', compact('teachers', 'inactiveTeachers', 'countries'));
    }

    /**
     * Crea un profesor SIN forzar la creación de usuario global.
     * Si existe un User con el mismo national_id+country_id o email, se vincula.
     * Si no, queda como perfil huérfano (user_id = null) y se envía invitación.
     */
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
        if ($request->filled('national_id') && Teacher::withoutGlobalScopes()
                ->where('national_id', $request->national_id)
                ->where('studio_id', $studioId)
                ->exists()) {
            $existingTeacher = Teacher::withTrashed()
                ->where('studio_id', $studioId)
                ->where('national_id', $request->national_id)
                ->first();

            if ($existingTeacher && $existingTeacher->trashed()) {
                $existingTeacher->restore();
                $existingTeacher->update($request->except(['national_id']));
                return back()->with('success', 'El profesor estaba en la papelera y ha sido reactivado con sus nuevos datos.');
            }
            return back()->withErrors(['national_id' => 'Este documento ya pertenece a un profesor activo en tu equipo.'])->withInput();
        }

        // 2. Validación
        $attributes = [
            'first_name'  => 'nombre',
            'email'       => 'correo electrónico',
            'country_id'  => 'país',
            'national_id' => 'documento de identidad',
            'phone'       => 'teléfono',
        ];

        $messages = [
            'required'    => 'El :attribute es obligatorio.',
            'email.email' => 'El :attribute debe ser una dirección válida.', // ✅ REGLA ESTRICTA
            'string'      => 'El :attribute debe ser texto.',
            'max'         => 'El :attribute no debe superar los :max caracteres.',
            'exists'      => 'El :attribute seleccionado no es válido.',
            'unique'      => 'Este :attribute ya está registrado en tu estudio para este país.',
        ];

        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id', 
            'national_id' => [
                'required', 'string', 'max:255', new ValidDocument($countryCode)
            ],
            'email' => [
                'nullable', 'email', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existingUser = User::where('email', $value)->first();
                    if ($existingUser && $existingUser->national_id !== $request->national_id) {
                        $fail('Este correo ya está registrado con otro documento.');
                    }
                }
            ],
            'phone'       => 'nullable|string|max:255',
        ], $messages, $attributes);

        // 3. BÚSQUEDA DE USUARIO GLOBAL (SIN CREAR)
        // Solo por national_id + country_id — el email no se usa para vincular
        $user = User::where('national_id', $request->national_id)
            ->where('country_id', $request->country_id)
            ->first();

        $teacherData = $request->all();
        $teacherData['studio_id'] = $studioId;
        $teacherData['user_id'] = $user ? $user->id : null;

        $teacher = Teacher::create($teacherData);

        // 4. DISPARO DE NOTIFICACIONES
        $studio = Studio::find($studioId);

        if ($user) {
            // Vinculado a un usuario existente
            try {
                Mail::to($user->email)->send(new UserLinkedAsTeacherMail($studio, $user->name));
                $user->notify(new TeacherAddedNotification($studio));
            } catch (\Exception $e) {
                Log::error('Fallo notificación vinculación profesor: ' . $e->getMessage());
            }
        } elseif ($request->filled('email')) {
            // Huérfano con email → invitación a registrarse
            try {
                Mail::to($request->email)->queue(new TeacherInvitationMail($studio, $teacher));
            } catch (\Exception $e) {
                Log::error('Fallo envío invitación profesor: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Profesor registrado correctamente.');
    }

    public function update(Request $request, $subdomain, Teacher $teacher)
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

        // 3. VALIDACIÓN BLINDADA — con mensajes en español
        $attributes = [
            'first_name'  => 'nombre',
            'country_id'  => 'país',
            'national_id' => 'documento de identidad',
            'email'       => 'correo electrónico',
            'phone'       => 'teléfono',
        ];

        $messages = [
            'required'    => 'El :attribute es obligatorio.',
            'email.email' => 'El :attribute debe ser una dirección válida.', // ✅ REGLA ESTRICTA
            'string'      => 'El :attribute debe ser texto.',
            'max'         => 'El :attribute no debe superar los :max caracteres.',
            'exists'      => 'El :attribute seleccionado no es válido.',
            'unique'      => 'Este :attribute ya está registrado en tu estudio para este país.',
        ];

        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id', 
            'national_id' => [
                'required',
                'string',
                'max:255',
                new ValidDocument($countryCode),
                Rule::unique('teachers', 'national_id')
                    ->ignore($teacher->id)
                    ->where(function ($query) use ($studioId, $request) {
                        return $query->where('studio_id', $studioId)
                                     ->where('country_id', $request->country_id);
                    })
            ],
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:255',
        ], $messages, $attributes);

        $teacher->update($request->all());

        return back()->with('success', 'Datos del profesor actualizados.');
    }

    public function restore($subdomain, $id)
    {
        $teacher = Teacher::withTrashed()->findOrFail($id);
        $teacher->restore();
        
        return back()->with('success', 'Profesor reactivado exitosamente.');
    }

    public function forceDelete($subdomain, $id)
    {
        $teacher = Teacher::withTrashed()->findOrFail($id);
        $teacher->forceDelete();
        
        return back()->with('success', 'Profesor eliminado permanentemente del sistema.');
    }
    
    public function destroy($subdomain, Teacher $teacher)
    {
        $teacher->delete();
        return back()->with('success', 'Profesor desactivado del equipo.');
    }
}
