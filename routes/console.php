<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Models\Studio;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ─── GUARDIÁN DEL TIEMPO (CRON JOB DE SUSCRIPCIONES) ───
Schedule::call(function () {
    
    // =========================================================================
    // PASO 1: INICIO DEL PERÍODO DE GRACIA (Recién expirados)
    // =========================================================================
    // Buscamos estudios Pro/Elite cuya fecha caducó ayer/hoy.
    $recentlyExpiredStudios = Studio::whereIn('subscription_status', ['pro', 'elite'])
        ->where('subscription_expires_at', '<', now())
        ->get();

    foreach ($recentlyExpiredStudios as $studio) {
        // Lo pasamos al estado de gracia. Como no es 'free', NO se le cobra el 5% aún.
        $studio->update([
            'subscription_status' => 'past_due'
        ]);
        
        // Disparamos la notificación ÚNICA de advertencia
        if ($studio->user) {
            try {
                // Aquí llamarás a tu Mailable y Notificación (Debes crearlos luego)
                // $studio->user->notify(new \App\Notifications\GracePeriodStartedNotification($studio));
                // Mail::to($studio->user->email)->send(new \App\Mail\GracePeriodStartedMail($studio));
                Log::info("Estudio {$studio->name} entró en período de gracia de 5 días.");
            } catch (\Exception $e) {
                Log::error('Error notificando inicio de período de gracia: ' . $e->getMessage());
            }
        }
    }

    // =========================================================================
    // PASO 2: FIN DEL PERÍODO DE GRACIA (Downgrade Definitivo a Free)
    // =========================================================================
    // Buscamos estudios 'past_due' cuya fecha de caducidad fue hace MÁS de 5 días.
    $gracePeriodEndedStudios = Studio::where('subscription_status', 'past_due')
        ->where('subscription_expires_at', '<', now()->subDays(5))
        ->get();

    foreach ($gracePeriodEndedStudios as $studio) {
        // Se acabó el tiempo. Pasa a 'free', el MercadoPagoService vuelve a retener el 5%.
        $studio->update([
            'subscription_status' => 'free',
            'mp_preapproval_id' => null
        ]);
        
        if ($studio->user) {
            try {
                // $studio->user->notify(new \App\Notifications\SubscriptionDowngradedNotification($studio));
                // Mail::to($studio->user->email)->send(new \App\Mail\SubscriptionDowngradedMail($studio));
                Log::info("Estudio {$studio->name} bajado a Free tras 5 días de mora.");
            } catch (\Exception $e) {
                Log::error('Error notificando downgrade a free: ' . $e->getMessage());
            }
        }
    }

})->dailyAt('00:05'); // Se ejecuta todos los días a las 12:05 AM