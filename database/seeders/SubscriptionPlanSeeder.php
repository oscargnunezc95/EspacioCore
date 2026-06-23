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
                'name'                 => 'Gratis',
                'slug'                 => 'free',
                'price'                => 0,
                'platform_fee_percent' => 5.00,
                'capacity_limit'       => null,
                'max_billing_cycles'   => null,
                'is_active'            => true
            ],
            [
                'name'                 => 'Crecimiento',
                'slug'                 => 'crecimiento',
                'price'                => 25000,
                'platform_fee_percent' => 2.50,
                'capacity_limit'       => null,
                'max_billing_cycles'   => null,
                'is_active'            => true
            ],
            [
                'name'                 => 'Pro',
                'slug'                 => 'pro',
                'price'                => 45000,
                'platform_fee_percent' => 1.50,
                'capacity_limit'       => null,
                'max_billing_cycles'   => null,
                'is_active'            => true,

            ],
            [
                'name'                 => 'Elite',
                'slug'                 => 'elite',
                'price'                => 89000,
                'platform_fee_percent' => 0.00,
                'capacity_limit'       => null,
                'max_billing_cycles'   => null,
                'is_active'            => true,
            ],
            [
                // Gatillo psicológico (FOMO) para "Early Adopters"
                'name'                 => 'Founder Elite',
                'slug'                 => 'founder-elite',
                'price'                => 15000,
                'platform_fee_percent' => 0.00,
                'capacity_limit'       => 7, // El sistema bloqueará nuevas suscripciones al llegar a 7
                'max_billing_cycles'   => 6,
                'is_active'            => true,
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