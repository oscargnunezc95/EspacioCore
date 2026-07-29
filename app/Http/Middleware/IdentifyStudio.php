<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Config;
use App\Models\Studio;

class IdentifyStudio
{
    public function handle(Request $request, Closure $next): Response
    {
        $subdomain = $request->route('subdomain');
        $studio = Studio::where('subdomain', $subdomain)->first();

        if (!$studio) {
            return redirect()->route('studios.index')->with('error', 'El estudio solicitado no existe.');
        }

        // 🔒 AUTORIZACIÓN: Solo el dueño del estudio puede acceder a la gestión interna.
        // El escaparate público (perfil, talleres, promos, calendario) no pasa por este
        // middleware — usa únicamente 'public.studio.status', por lo que sigue siendo
        // accesible para cualquier visitante (logueado o no).
        if (auth()->id() !== $studio->user_id) {
            abort(403, 'No tienes permiso para acceder a la gestión de este estudio.');
        }

        // LA CURA: Guardamos el ID en la configuración del Request, NO en la sesión web.
        // Esto muere automáticamente cuando la página termina de cargar.
        Config::set('tenant.studio_id', $studio->id);

        // TRUCO 1: Automatizar el parámetro {subdomain} en todas las rutas route()
        URL::defaults(['subdomain' => $subdomain]);

        // TRUCO 2: Compartir el objeto Studio con todas las vistas Blade
        View::share('currentStudio', $studio);

        return $next($request);
    }
}