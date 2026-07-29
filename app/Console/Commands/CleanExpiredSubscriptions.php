<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

/**
 * @deprecated El sistema de suscripciones SaaS fue reemplazado por Facturación
 *             Mensual por Uso (Floor-Capped Usage Pricing) en julio 2026.
 *             Este comando se conserva como no-op para evitar fallos en
 *             cron jobs existentes. Las facturas ahora se gestionan con
 *             `billing:generate` y el bloqueo por deuda con el middleware
 *             CheckStudioDebt.
 */
class CleanExpiredSubscriptions extends Command
{
    protected $signature = 'saas:clean-subscriptions';

    protected $description = '[DEPRECATED] Procesa expiraciones de suscripción. Migrado a billing:generate.';

    public function handle(): void
    {
        $this->warn('⚠️  saas:clean-subscriptions está deprecado.');
        $this->line('   El sistema de suscripciones SaaS fue reemplazado por Facturación por Uso.');
        $this->line('   Usa `php artisan billing:generate` para generar las facturas mensuales.');
        $this->line('   El bloqueo por morosidad ahora lo maneja el middleware CheckStudioDebt.');
        $this->line('   Este comando no realiza ninguna acción.');
    }
}
