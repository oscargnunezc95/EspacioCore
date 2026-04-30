<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Studio;
use Symfony\Component\HttpFoundation\Response;

class IdentifyStudio
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Extraemos el subdominio de la URL actual
        $subdomain = $request->route('subdomain');
        
        // 2. Buscamos el estudio en la BD
        $studio = Studio::where('subdomain', $subdomain)->first();

        // Si alguien escribe un subdominio que no existe, lanzamos error 404
        if (!$studio) {
            abort(404, 'Este estudio no existe en EspacioCore.');
        }

        // Seguridad: Evitar que una dueña entre al subdominio de otra
        if (auth()->check() && $studio->user_id !== auth()->id()) {
            abort(403, 'No tienes permisos para administrar este estudio.');
        }

        // 3. LA MAGIA: Guardamos el ID en la sesión para los Global Scopes
        session(['current_studio_id' => $studio->id]);

        // Olvidamos el parámetro en la ruta para que no ensucie los controladores
        $request->route()->forgetParameter('subdomain');

        return $next($request);
    }
}