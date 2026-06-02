<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\UserDependent;
use App\Models\Country;
use App\Services\DocumentService;
use App\Rules\ValidDocument;

class GoogleController extends Controller
{
    /**
     * Redirige al usuario a los servidores de Google.
     */
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Maneja la respuesta de Google.
     */
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // 1. Intentamos buscar si ya existe el usuario
            $user = User::where('google_id', $googleUser->id)
                        ->orWhere('email', $googleUser->email)
                        ->first();

            if ($user) {
                if (!$user->google_id) {
                    $user->update(['google_id' => $googleUser->id]);
                }
                
                Auth::login($user);
                // Redirigimos al Lobby de estudios, no a 'dashboard' porque eso exige subdominio
                return redirect()->route('studios.index'); 
            }

            // 2. Si NO existe, guardamos los datos en sesión temporal y vamos a completar el perfil
            session([
                'google_user_data' => [
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                ]
            ]);

            return redirect()->route('auth.google.complete');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Hubo un problema comunicándose con Google.');
        }
    }

    /**
     * Muestra la vista para pedir el RUT y País.
     */
    public function completeProfile()
    {
        // Si no hay datos de Google en sesión, lo devolvemos al login
        if (!session()->has('google_user_data')) {
            return redirect()->route('login');
        }

        $countries = Country::orderBy('name')->get();
        
        return view('auth.google-complete', compact('countries'));
    }

    /**
     * Guarda los datos finales y loguea al usuario.
     */
    public function storeCompleteProfile(Request $request)
    {
        $googleData = session('google_user_data');

        if (!$googleData) {
            return redirect()->route('login');
        }

        // 1. OBTENER EL CÓDIGO DEL PAÍS (Elegancia PHP 8)
        $countryCode = Country::find($request->country_id)?->code ?? 'CL';

        // 2. ESTANDARIZAR EL DOCUMENTO ANTES DE VALIDAR
        if ($request->filled('national_id')) {
            $request->merge([
                'national_id' => DocumentService::standardize($request->national_id, $countryCode)
            ]);
        }

        // 3. VALIDACIÓN BLINDADA — con mensajes en español
        $attributes = [
            'country_id'  => 'país',
            'national_id' => 'documento de identidad',
        ];

        $messages = [
            'required'            => 'El :attribute es obligatorio.',
            'unique'              => 'Este :attribute ya está registrado.',
            'unique_national_id_country' => 'Este documento ya está registrado en el país seleccionado.',
            'exists'              => 'El :attribute seleccionado no es válido.',
            'string'              => 'El :attribute debe ser texto.',
            'max'                 => 'El :attribute no debe superar los :max caracteres.',
        ];

        $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'national_id' => [
                'required', 
                'string', 
                'max:255',
                new ValidDocument($countryCode),
                // Validación compuesta: único dentro del mismo país
                Rule::unique('users', 'national_id')->where('country_id', $request->country_id),
            ],
        ], $messages, $attributes);

        // Creamos el usuario disparando la "Magia" (El mutador en User.php hará su doble chequeo aquí)
        $user = User::create([
            'name' => $googleData['name'],
            'email' => $googleData['email'],
            'google_id' => $googleData['google_id'],
            'country_id' => $request->country_id,
            'national_id' => $request->national_id,
            'password' => bcrypt(Str::random(16)), 
        ]);

        // ─── DETECCIÓN DE DEPENDIENTE PRE-EXISTENTE (ANTES del barrido) ─────
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

            Auth::login($user);
            return redirect()->route('profile.dependent.decision');
        }

        // ─── BARRIBO DE RECLAMACIÓN (solo si NO es dependiente pre-existente) ─
        $this->claimOrphanProfiles($user);

        session()->forget('google_user_data');
        
        Auth::login($user);

        // Redirigimos al Explorador (o al Lobby de estudios)
        return redirect()->route('explore');
    }

    /**
     * Barrido de Reclamación: busca fichas huérfanas en students y teachers
     * y las vincula al nuevo User creado.
     * Solo empareja por national_id + country_id — el email no se usa.
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
                Log::info("Barrido Google: {$total} fichas huérfanas vinculadas al usuario #{$user->id} ({$user->email})");
            }
        } catch (\Exception $e) {
            Log::error('Error en barrido de reclamación (Google): ' . $e->getMessage());
        }
    }
}
