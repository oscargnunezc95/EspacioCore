<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Country;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\DocumentService;
use App\Rules\ValidDocument;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        $countries = Country::orderBy('name')->get();
        return view('auth.register', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        // 1. PHP 8 Nullsafe operator: Código en 1 sola línea
        $countryCode = Country::find($request->country_id)?->code ?? 'CL';

        // 2. Limpiamos el request para que Rule::unique funcione correctamente
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. Validación Blindada
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'country_id' => ['required', 'exists:countries,id'],
            'national_id' => [
                'required', 
                'string', 
                'max:255', 
                new ValidDocument($countryCode),
                // Reemplaza tu 'if' manual por esta regla nativa
                Rule::unique('users', 'national_id')->where('country_id', $request->country_id)
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

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