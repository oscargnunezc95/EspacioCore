<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            // Condición de búsqueda (si encuentra este email, actualiza; si no, crea)
            ['email' => 'oscargnunezc95@gmail.com'],
            
            // Valores a insertar o actualizar
            [
                'name' => 'Oscar Nuñez (SuperAdmin)',
                'password' => Hash::make('El.Soporte%759763'),
                'email_verified_at' => now(),
                'national_id' => '18802107-7',
                'country_id' => 1, // Chile
                'is_super_admin' => true, // La llave maestra
            ]
        );

        $this->command->info('Usuario SuperAdmin configurado correctamente.');
    }
}