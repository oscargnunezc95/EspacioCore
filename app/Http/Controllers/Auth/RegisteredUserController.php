<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Services\DocumentService; // <-- IMPORTACIÓN DE LIMPIEZA
use App\Rules\ValidDocument;      // <-- IMPORTACIÓN DE VALIDACIÓN

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $countries = Country::orderBy('name')->get();
        return view('auth.register', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. OBTENER EL CÓDIGO DEL PAÍS DESDE EL ID (Por defecto 'CL')
        $countryCode = 'CL';
        if ($request->filled('country_id')) {
            $country = Country::find($request->country_id);
            $countryCode = $country ? $country->code : 'CL'; // Asume que tu tabla countries tiene una columna 'code'
        }

        // 2. ESTANDARIZAR EL RUT
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. VALIDACIÓN (Con la nueva regla)
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'country_id' => ['required', 'exists:countries,id'],
            'national_id' => ['required', 'string', 'max:255', new ValidDocument($countryCode)], 
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Validación manual de unicidad usando el RUT ya limpio
        if (User::where('national_id', $request->national_id)->where('country_id', $request->country_id)->exists()) {
            return back()->withErrors(['national_id' => 'Este documento ya está registrado en este país.'])->withInput();
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country_id' => $request->country_id,
            'national_id' => $request->national_id,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('explore', absolute: false));
    }
}