<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;
use App\Models\Teacher;
use App\Services\DocumentService;
use App\Rules\ValidDocument;
use App\Models\Country; 
use Illuminate\Support\Facades\Mail;
use App\Models\Studio;
use App\Mail\TeacherInvitationMail;
use App\Mail\UserLinkedToStudioMail;
use App\Notifications\TeacherAddedNotification;
use App\Services\TenantIdentityService; // 👈 NUEVO

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

    public function store(Request $request, $subdomain, TenantIdentityService $identityService) // 👈 Inyección
    {
        $studioId = Config::get('tenant.studio_id');
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 1. VERIFICACIÓN RÁPIDA: ¿Ya existe en este estudio?
        if ($identityService->isTeacherInStudio($request->national_id, $studioId)) {
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

        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id', 
            'national_id' => [
                'nullable', 'string', 'max:255', new ValidDocument($countryCode)
            ],
            'email' => [
                'nullable', 'email', 'max:255',
                function ($attribute, $value, $fail) use ($request) {
                    $existingUser = \App\Models\User::where('email', $value)->first();
                    // Si el correo existe pero le pertenece a un RUT distinto, bloqueamos.
                    if ($existingUser && $existingUser->national_id !== $request->national_id) {
                        $fail('Este correo ya está registrado con otro documento.');
                    }
                }
            ],
            'phone'       => 'nullable|string|max:255',
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

        $teacherData = $request->all();
        $teacherData['studio_id'] = $studioId;
        $teacherData['user_id'] = $user ? $user->id : null;

        $teacher = Teacher::create($teacherData);

        // 3. DISPARO DE NOTIFICACIONES
        if ($user) {
            try {
                $studio = Studio::find($studioId);
                
                if ($identity['is_new']) {
                    Mail::to($user->email)->send(new TeacherInvitationMail($studio, $teacher, $identity['temp_password']));
                } else {
                    Mail::to($user->email)->send(new UserLinkedToStudioMail($studio, $user->name));
                }
                
                $user->notify(new TeacherAddedNotification($studio));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Fallo onboarding profesor: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Profesor registrado y notificado correctamente.');
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

        // 3. VALIDACIÓN BLINDADA
        $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'nullable|string|max:255',
            'country_id'  => 'required|exists:countries,id', 
            'national_id' => [
                'nullable',
                'string',
                'max:255',
                new ValidDocument($countryCode), // Usamos el código real seleccionado
                Rule::unique('teachers', 'national_id')
                    ->ignore($teacher->id)
                    ->where(function ($query) use ($studioId) {
                        return $query->where('studio_id', $studioId);
                    })
            ],
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:255',
        ]);

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