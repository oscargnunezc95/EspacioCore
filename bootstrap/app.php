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
            'identify.studio'        => \App\Http\Middleware\IdentifyStudio::class,
            'check.profile'          => \App\Http\Middleware\CheckProfileCompletion::class,
            'dependent.decision'     => \App\Http\Middleware\EnsureDependentDecisionResolved::class,
            'super.admin'            => \App\Http\Middleware\IsSuperAdmin::class,
        ]);

        // Redirección dinámica basada en el nombre de la ruta (Best Practice)
        $middleware->redirectUsersTo(fn () => route('global.classes.student'));

        // Exclusiones CSRF (Deben mantenerse como URIs/Strings)
        $middleware->validateCsrfTokens(except: [
            'api/webhooks/mercadopago',
            'webhooks/mercadopago',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();