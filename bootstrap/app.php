<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Registramos los alias para usarlos en web.php
        $middleware->alias([
            'identify.studio' => \App\Http\Middleware\IdentifyStudio::class,
            // NUESTRO NUEVO GUARDIA DE SEGURIDAD
            'check.profile'   => \App\Http\Middleware\CheckProfileCompletion::class,
        ]);

        // Le dice a Laravel que redirija a los usuarios logueados al Lobby, no al dashboard genérico.
        $middleware->redirectUsersTo('/mis-estudios');

        // 👇 EL PARCHE DE QA PARA PERMITIR LA ENTRADA DEL WEBHOOK 👇
        $middleware->validateCsrfTokens(except: [
            'api/webhooks/mercadopago', // Usa esta si la ruta está en api.php
            'webhooks/mercadopago',     // Agrega esta también por si la pusiste en web.php
            // También puedes usar comodines como: '*/webhooks/mercadopago'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();