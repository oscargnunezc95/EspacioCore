<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsSuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * Verifica que el usuario autenticado tenga is_super_admin == true.
     * Si no, aborta con error 403 (Forbidden).
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->is_super_admin) {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
