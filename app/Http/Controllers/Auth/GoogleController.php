<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule; // <-- NUEVO
use App\Models\User;
use App\Models\Country;
use App\Services\DocumentService; // <-- NUEVO
use App\Rules\ValidDocument;      // <-- NUEVO

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

        // 3. VALIDACIÓN BLINDADA (Usamos ValidDocument y Rule::unique)
        $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'national_id' => [
                'required', 
                'string', 
                'max:255',
                new ValidDocument($countryCode),
                Rule::unique('users', 'national_id')->where('country_id', $request->country_id)
            ],
        ]);

        // Creamos el usuario disparando la "Magia" (El mutador en User.php hará su doble chequeo aquí)
        $user = User::create([
            'name' => $googleData['name'],
            'email' => $googleData['email'],
            'google_id' => $googleData['google_id'],
            'country_id' => $request->country_id,
            'national_id' => $request->national_id,
            'password' => bcrypt(Str::random(16)), 
        ]);

        session()->forget('google_user_data');
        
        Auth::login($user);

        // Redirigimos al Explorador (o al Lobby de estudios)
        return redirect()->route('explore');
    }
}