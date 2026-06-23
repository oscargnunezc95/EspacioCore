<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\UserDependent;
use App\Models\Country;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
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
        $countryCode = Country::find($request->country_id)?->code ?? 'CL';

        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        $attributes = [
            'name'              => 'nombre completo',
            'email'             => 'correo electrónico',
            'country_id'        => 'país',
            'national_id'       => 'documento de identidad',
            'password'          => 'contraseña',
            'terms_accepted'    => 'términos y condiciones',
            'privacy_accepted'  => 'política de privacidad',
        ];

        $messages = [
            'required'            => 'El :attribute es obligatorio.',
            'email'               => 'El :attribute debe ser una dirección válida.',
            'unique'              => 'Este :attribute ya está registrado.',
            'confirmed'           => 'La confirmación de :attribute no coincide.',
            'max'                 => 'El :attribute no debe superar los :max caracteres.',
            'min'                 => 'El :attribute debe tener al menos :min caracteres.',
            'exists'              => 'El :attribute seleccionado no es válido.',
            'string'              => 'El :attribute debe ser texto.',
            'password.mixed_case' => 'La contraseña debe contener al menos una letra mayúscula y una minúscula.',
            'password.numbers'    => 'La contraseña debe contener al menos un número.',
            'password.symbols'    => 'La contraseña debe contener al menos un símbolo.',
            'password.uncompromised' => 'Esta contraseña ha aparecido en filtraciones de datos. Elige otra.',
        ];

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'country_id' => ['required', 'exists:countries,id'],
            'national_id' => [
                'required', 
                'string', 
                'max:255', 
                new ValidDocument($countryCode),
                Rule::unique('users', 'national_id')->where('country_id', $request->country_id),
            ],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms_accepted'   => ['required', 'accepted'],
            'privacy_accepted' => ['required', 'accepted'],
        ], $messages, $attributes);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'country_id' => $request->country_id,
            'national_id' => $request->national_id,
            'password' => Hash::make($request->password),
            'terms_accepted_at'   => now(),
            'privacy_accepted_at' => now(),
        ]);

        // ─── DETECCIÓN DE DEPENDIENTE PRE-EXISTENTE (ANTES del barrido) ─────
        // Verificar si alguien ya tenía registrado este documento como familiar
        $existingDependent = UserDependent::where('national_id', $user->national_id)
            ->where('country_id', $user->country_id)
            ->where('user_id', '!=', $user->id)
            ->first();

        if ($existingDependent) {
            // Guardar en BD para que persista aunque se pierda la sesión
            $user->update([
                'dependent_decision_pending'   => true,
                'dependent_decision_owner_id'  => $existingDependent->user_id,
            ]);

            event(new Registered($user));
            Auth::login($user);
            return redirect()->route('profile.dependent.decision');
        }

        // ─── BARRIBO DE RECLAMACIÓN (solo si NO es dependiente pre-existente) ─
        $this->claimOrphanProfiles($user);

        event(new Registered($user));
        Auth::login($user);

        return redirect(route('explore', absolute: false));
    }

    /**
     * Barrido de Reclamación: busca fichas huérfanas en students y teachers
     * y las vincula al nuevo User creado.
     * Solo empareja por national_id + country_id.
     */
    private function claimOrphanProfiles(User $user): void
    {
        try {
            $studentsUpdated = Student::where('national_id', $user->national_id)
                ->where('country_id', $user->country_id)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);

            $teachersUpdated = Teacher::where('national_id', $user->national_id)
                ->where('country_id', $user->country_id)
                ->whereNull('user_id')
                ->update(['user_id' => $user->id]);

            $total = $studentsUpdated + $teachersUpdated;
            if ($total > 0) {
                Log::info("Barrido de reclamación: {$total} fichas huérfanas vinculadas al usuario #{$user->id} ({$user->email})");
            }
        } catch (\Exception $e) {
            Log::error('Error en barrido de reclamación: ' . $e->getMessage());
        }
    }
}
