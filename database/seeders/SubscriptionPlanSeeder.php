<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SubscriptionPlan;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name'                 => 'Crecimiento',
                'slug'                 => 'crecimiento',
                'price'                => 25000,
                'platform_fee_percent' => 2.50,
                'capacity_limit'       => null,
                'max_billing_cycles'   => null,
                'is_active'            => true,
                'features'             => json_encode([
                    'Gestión de clases y reservas',
                    'Portal de alumnos e instructores',
                    'Integración de pagos con Mercado Pago',
                    'Soporte estándar'
                ]),
            ],
            [
                'name'                 => 'Pro',
                'slug'                 => 'pro',
                'price'                => 45000,
                'platform_fee_percent' => 1.50,
                'capacity_limit'       => null,
                'max_billing_cycles'   => null,
                'is_active'            => true,
                'features'             => json_encode([
                    'Todo lo del plan Crecimiento',
                    'Módulo avanzado de promociones y combos',
                    'Panel de liquidaciones automáticas',
                    'Soporte prioritario'
                ]),
            ],
            [
                'name'                 => 'Elite',
                'slug'                 => 'elite',
                'price'                => 89000,
                'platform_fee_percent' => 0.00,
                'capacity_limit'       => null,
                'max_billing_cycles'   => null,
                'is_active'            => true,
                'features'             => json_encode([
                    'Todo lo del plan Pro',
                    'Comisión 0% en transacciones (Cobro directo)',
                    'Estadísticas avanzadas y reportes',
                    'Asesoría de negocio'
                ]),
            ],
            [
                // Gatillo psicológico (FOMO) para "Early Adopters"
                'name'                 => 'Founder Elite',
                'slug'                 => 'founder-elite',
                'price'                => 15000,
                'platform_fee_percent' => 0.00,
                'capacity_limit'       => 7, // El sistema bloqueará nuevas suscripciones al llegar a 7
                'max_billing_cycles'   => null,
                'is_active'            => true,
                'features'             => json_encode([
                    'Todos los beneficios del plan Elite',
                    'Precio vitalicio congelado',
                    'Comisión 0% permanente',
                    'Acceso anticipado a nuevas funcionalidades'
                ]),
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}