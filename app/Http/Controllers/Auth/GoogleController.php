<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    // 1. Redirige al usuario a la pantalla de Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // 2. Google nos devuelve al usuario aquí
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Buscamos si el correo ya existe (quizás se registró manual antes) o creamos uno nuevo
            $user = User::updateOrCreate(
                ['email' => $googleUser->email],
                [
                    'name' => $googleUser->name,
                    'google_id' => $googleUser->id,
                    'password' => null, // No necesita clave
                    'email_verified_at' => now(), // Google ya verificó este correo
                ]
            );

            Auth::login($user);

            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Ocurrió un error al autenticar con Google.']);
        }
    }
}