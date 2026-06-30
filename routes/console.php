<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── GUARDIÁN DEL TIEMPO (CRON JOB DE SUSCRIPCIONES) ───
// Procesa expiraciones naturales y bloqueos por morosidad > 5 días.
// Toda la lógica está centralizada en Studio::checkAndManageLifecycle().
Schedule::command('saas:clean-subscriptions')->daily();