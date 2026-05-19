<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Config;
use App\Models\Teacher;
use App\Services\DocumentService;
use App\Rules\ValidDocument;
use App\Models\Country; 

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

    public function store(Request $request, $subdomain)
    {
        $studioId = Config::get('tenant.studio_id');
        
        // 1. OBTENER EL CÓDIGO DEL PAÍS (Elegancia PHP 8: en 1 sola línea)
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        // 2. ESTANDARIZAR EL DOCUMENTO ANTES DE VALIDAR (Defensa en Profundidad)
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);

            // LÓGICA DE RESCATE (Papelera de Profesores)
            $existingTeacher = Teacher::withTrashed()
                ->where('studio_id', $studioId)
                ->where('national_id', $request->national_id)
                ->first();

            if ($existingTeacher) {
                if ($existingTeacher->trashed()) {
                    $existingTeacher->restore();
                    $existingTeacher->update($request->except(['national_id']));
                    return back()->with('success', 'El profesor estaba en la papelera y ha sido reactivado con sus nuevos datos.');
                }
                return back()->withErrors(['national_id' => 'Este documento ya pertenece a un profesor activo en tu equipo.'])->withInput();
            }
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
                Rule::unique('teachers', 'national_id')->where(function ($query) use ($studioId) {
                    return $query->where('studio_id', $studioId);
                })
            ],
            'email'       => 'nullable|email|max:255',
            'phone'       => 'nullable|string|max:255',
        ]);

        Teacher::create($request->all());

        return back()->with('success', 'Profesor registrado. Si el documento coincide con una cuenta global, se vinculará automáticamente.');
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