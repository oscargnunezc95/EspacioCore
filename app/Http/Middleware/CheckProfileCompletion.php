<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckProfileCompletion
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        // Si el usuario está logueado pero le faltan los datos maestros de identidad
        if ($user && (is_null($user->national_id) || is_null($user->country_id))) {
            
            // No lo redirigimos si ya está en la página de completar o intentando guardar
            if (!$request->routeIs('auth.google.complete') && !$request->routeIs('auth.google.store') && !$request->routeIs('logout')) {
                return redirect()->route('auth.google.complete')
                                ->with('info', 'Debes completar tu perfil para continuar.');
            }
        }

        return $next($request);
    }
}
