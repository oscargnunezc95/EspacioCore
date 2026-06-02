<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureDependentDecisionResolved
{
    /**
     * Si el usuario tiene una decisión de dependiente pendiente,
     * bloquea el acceso a cualquier ruta que no sea de decisión.
     * Debe ejecutarse DESPUÉS de auth pero ANTES de verified.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Rutas permitidas durante la decisión pendiente
        $allowedRoutes = [
            'profile.dependent.decision',
            'profile.dependent.unlink',
            'profile.dependent.share',
            'logout',
            'profile.destroy',
        ];

        $currentRoute = $request->route()?->getName();

        if ($user->dependent_decision_pending && ! in_array($currentRoute, $allowedRoutes)) {
            return redirect()->route('profile.dependent.decision');
        }

        return $next($request);
    }
}
