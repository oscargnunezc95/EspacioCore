<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Student; // IMPORTANTE
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => bcrypt(str()->random(16)),
                    'email_verified_at' => now(), // Google ya está verificado
                ]
            );

            // MAGIA 3: Sincronización Automática (Lado de la Alumna)
            // Actualizamos todas las fichas huérfanas que tengan este correo
            Student::where('email', $user->email)
                   ->whereNull('user_id')
                   ->update(['user_id' => $user->id]);

            Auth::login($user);

            // Redirigir al panel
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Error al iniciar sesión con Google.');
        }
    }
}