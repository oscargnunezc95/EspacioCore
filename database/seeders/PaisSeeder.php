<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Models\User;
use App\Models\Country;

class PaisSeeder extends Seeder
{
    public function run(): void
    {
        Country::firstOrCreate(
            [
                'name' => 'Chile',
                'code' => 'CL',
                'tax_id_label' => 'RUT',
                'tax_id_regex' => '^(\d{7,8}[0-9Kk])$',
                'currency_code' => 'CLP',
                'currency_symbol' => '$',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        Country::firstOrCreate(
            [
                'name' => 'Otro / Internacional',
                'code' => 'OT',
                'tax_id_label' => 'Pasaporte / ID',
                'tax_id_regex' => '^.+$',
                'currency_code' => 'USD',
                'currency_symbol' => 'US$',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
        // 1. Usuario Administrador Principal
        $admin = User::firstOrCreate(
            ['email' => 'oscargnunezc18@gmail.com'],
            [
                'name' => 'Oscar Nuñez',
                'password' => Hash::make('qwerqwer'),
                'email_verified_at' => now(),
                'national_id' => '18802107-7',
                'country_id' => 1,
            ]
        );
    }
}