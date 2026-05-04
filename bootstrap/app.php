<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registramos el alias para usarlo en web.php como 'identify.studio'
        $middleware->alias([
            'identify.studio' => \App\Http\Middleware\IdentifyStudio::class,
        ]);

        // ¡ESTA ES LA LÍNEA MÁGICA QUE FALTA!
        // Le dice a Laravel que redirija a los usuarios logueados al Lobby, no al dashboard genérico.
        $middleware->redirectUsersTo('/mis-estudios');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();