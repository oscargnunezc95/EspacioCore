<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── FACTURACIÓN MENSUAL POR USO (FLOOR-CAPPED USAGE PRICING) ───
// Se ejecuta el día 1 de cada mes a las 00:01 hrs.
// Genera facturas para todos los estudios basadas en las ventas del mes anterior.
Schedule::command('billing:generate')
    ->monthlyOn(1, '00:01')
    ->withoutOverlapping()
    ->runInBackground()
    ->appendOutputTo(storage_path('logs/billing-generate.log'));

// ─── LIMPIEZA DE SUSCRIPCIONES (DESHABILITADA) ───
// El sistema de suscripciones SaaS fue reemplazado por Facturación por Uso.
// Schedule::command('saas:clean-subscriptions')->daily();