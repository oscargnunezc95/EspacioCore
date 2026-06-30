<?php

namespace App\Console\Commands;

use App\Models\Studio;
use Illuminate\Console\Command;

class CleanExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:clean-subscriptions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procesa expiraciones y bloqueos por morosidad de más de 5 días de forma masiva.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // Buscamos todos los estudios evaluables:
        // - Aquellos en estado 'past_due' (posible bloqueo por morosidad)
        // - Aquellos cuya suscripción ya expiró y no son 'free'
        $studios = Studio::where('subscription_status', 'past_due')
            ->orWhere(function ($q) {
                $q->where('subscription_expires_at', '<=', now())
                  ->where('subscription_status', '!=', 'free');
            })
            ->get();

        if ($studios->isEmpty()) {
            $this->info('No se encontraron estudios que requieran limpieza de suscripción.');
            return;
        }

        $this->info("Procesando {$studios->count()} estudio(s)...");

        $processed = 0;

        foreach ($studios as $studio) {
            $estadoAnterior = $studio->subscription_status;
            $planAnterior    = $studio->subscription_plan_id;

            $studio->checkAndManageLifecycle();

            // Refrescamos para ver si hubo cambios
            $studio->refresh();

            if ($estadoAnterior !== $studio->subscription_status || $planAnterior !== $studio->subscription_plan_id) {
                $processed++;
                $this->line(
                    " ✓ Estudio #{$studio->id} «{$studio->name}»: " .
                    "{$estadoAnterior} → {$studio->subscription_status}"
                );
            }
        }

        $this->info("Limpieza completada. {$processed} estudio(s) actualizado(s).");
    }
}
