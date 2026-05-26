<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserDependent;
use App\Models\Country;
use App\Services\DocumentService;
use Illuminate\Validation\Rule;
use App\Rules\ValidDocument;

class FamilyController extends Controller
{
    public function index()
    {
        $dependents = Auth::user()->dependents()->orderBy('first_name')->get();
        // Enviamos los países a la vista para el dropdown
        $countries = Country::orderBy('name', 'asc')->get(); 
        
        return view('profile.family.index', compact('dependents', 'countries'));
    }

    public function store(Request $request)
    {
        // 1. Obtener el código del país dinámicamente
        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        // 2. Estandarizar antes de validar
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. Validación Blindada
        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'country_id'   => 'required|exists:countries,id', 
            'national_id'  => [
                'nullable', 'string', 'max:255',
                new ValidDocument($countryCode), // 👈 Validación estricta según el país
                
                // Candado A: Único dentro del grupo familiar
                Rule::unique('user_dependents', 'national_id')->where('user_id', Auth::id()),
                
                // Candado B: Que no choque con un usuario titular global
                function ($attribute, $value, $fail) {
                    $isGlobalUser = \App\Models\User::where('national_id', $value)->exists();
                    if ($isGlobalUser) {
                        $fail('Este documento ya corresponde a un usuario titular independiente en la plataforma.');
                    }
                }
            ],
            'relationship' => 'nullable|string|max:100',
        ]);

        // 4. Inserción Limpia
        Auth::user()->dependents()->create($validated);

        return back()->with('success', 'Familiar registrado correctamente.');
    }

    public function update(Request $request, UserDependent $dependent)
    {
        // Seguridad: ¿Es realmente su familiar?
        if ($dependent->user_id !== Auth::id()) abort(403);

        $countryCode = Country::find($request->country_id)?->code ?? 'OT';

        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'nullable|string|max:255',
            'country_id'   => 'required|exists:countries,id',
            'national_id'  => [
                'nullable', 'string', 'max:255',
                new ValidDocument($countryCode),
                
                // Ignorar a sí mismo en la actualización
                Rule::unique('user_dependents', 'national_id')
                    ->ignore($dependent->id)
                    ->where('user_id', Auth::id()),
                
                function ($attribute, $value, $fail) {
                    $isGlobalUser = \App\Models\User::where('national_id', $value)->exists();
                    if ($isGlobalUser) {
                        $fail('Este documento ya corresponde a un usuario titular independiente en la plataforma.');
                    }
                }
            ],
            'relationship' => 'nullable|string|max:100',
        ]);

        $dependent->update($validated);

        return back()->with('success', 'Datos del familiar actualizados.');
    }

    public function destroy(UserDependent $dependent)
    {
        if ($dependent->user_id !== Auth::id()) abort(403);
        $dependent->delete();
        return back()->with('success', 'Familiar removido de tu cuenta.');
    }
}