<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Studio;

class UserStudioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Creamos tu usuario administrador
        // Usamos firstOrCreate para que no dé error si ejecutas el seeder varias veces
        $user = User::firstOrCreate(
            ['email' => 'oscargnunezc18@gmail.com'],
            [
                'name' => 'Oscar Nuñez',
                'password' => Hash::make('qwerqwer'), // Encriptación obligatoria de Laravel
                'email_verified_at' => now(),
            ]
        );

        // 2. Creamos el estudio "Gravedad Zero" asignado a tu usuario
        $studioName = 'Gravedad Zero';
        
        Studio::firstOrCreate(
            ['subdomain' => Str::slug($studioName)], // Buscará 'gravedad-zero'
            [
                'user_id'   => $user->id,
                'name'      => $studioName,
                // Agregamos datos base para que el mapa y los filtros tengan con qué jugar
                'address'   => 'Dirección por definir',
                'city'      => 'Puerto Montt',
                'region'    => 'Los Lagos',
                'country'   => 'Chile',
                'latitude'  => -41.46930000, 
                'longitude' => -72.94230000,
            ]
        );
    }
}