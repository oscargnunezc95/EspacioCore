<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. DATOS ESTRUCTURALES (Corren siempre: local, testing, production)
        $this->call([
            PaisSeeder::class,
            AreaDisciplineSeeder::class,
            SuperAdminSeeder::class,
            //SubscriptionPlanSeeder::class,
        ]);

        // 2. DATOS DE PRUEBA (Corren SOLO en tu computadora local)
        if (app()->environment('local', 'testing')) {
            $this->call([
                PruebaSeeder::class,
                LandingDemoSeeder::class,
            ]);
            
            $this->command->info('Seeders de prueba ejecutados correctamente en entorno local.');
        } else {
            $this->command->warn('Se ha omitido PruebaSeeder porque no estás en entorno local.');
        }
    }
}