<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Studio;
use App\Models\Workshop;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Country;

class UserStudioSeeder extends Seeder
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
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Otro / Internacional',
                'code' => 'OT',
                'tax_id_label' => 'Pasaporte / ID',
                'tax_id_regex' => '^.+$',
                'currency_code' => 'USD',
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

        // 2. Configuración de Estudios y Talleres (Adaptado a Schedules Múltiples)
        // Días: 0=Dom, 1=Lun, 2=Mar, 3=Mié, 4=Jue, 5=Vie, 6=Sáb
        $studiosData = [
            [
                'name' => 'Flow Pesado',
                'address' => 'Urriola 1404',
                'teacher' => 'Carol Igor',
                'workshop' => 'Urbano Básico',
                'color' => 'purple',
                'schedules' => [
                    ['day' => 1, 'time' => '18:00:00', 'max_students' => 15], // Lunes
                    ['day' => 3, 'time' => '18:00:00', 'max_students' => 15], // Miércoles
                ]
            ],
            [
                'name' => 'Movimiento Aéreo',
                'address' => 'Monseñor Ramon Munita 1553, Local 18',
                'teacher' => 'Ana Puentes',
                'workshop' => 'Flexibilidad',
                'color' => 'blue',
                'schedules' => [
                    ['day' => 2, 'time' => '19:30:00', 'max_students' => 10], // Martes
                    ['day' => 4, 'time' => '09:00:00', 'max_students' => 10], // Jueves (Bloque AM)
                ]
            ],
            [
                'name' => 'KS Heels',
                'address' => 'Avenida Parque Industrial 409',
                'teacher' => 'Karen Soto',
                'workshop' => 'Heels Flexibility',
                'color' => 'rose',
                'schedules' => [
                    ['day' => 5, 'time' => '20:00:00', 'max_students' => 20], // Viernes
                ]
            ],
            [
                'name' => 'Soul Aereal',
                'address' => 'Mall Paseo La Paloma',
                'teacher' => 'Toti Pulga',
                'workshop' => 'Telas Pro',
                'color' => 'emerald',
                'schedules' => [
                    ['day' => 6, 'time' => '10:30:00', 'max_students' => 8],  // Sábado AM
                    ['day' => 6, 'time' => '17:00:00', 'max_students' => 12], // Sábado PM
                ]
            ],
        ];

        // Alumnas base (se repetirán en los estudios para simular usuarios)
        $alumnasBase = [
            ['first' => 'Valentina', 'last' => 'Rojas'],
            ['first' => 'Camila', 'last' => 'Soto'],
            ['first' => 'Javiera', 'last' => 'Paz'],
            ['first' => 'Isidora', 'last' => 'Vera'],
            ['first' => 'Fernanda', 'last' => 'Lagos'],
            ['first' => 'Antonia', 'last' => 'Silva'],
            ['first' => 'Martina', 'last' => 'Diaz'],
        ];

        foreach ($studiosData as $data) {
            // A) Crear/Buscar el Estudio
            $studio = Studio::firstOrCreate(
                ['subdomain' => Str::slug($data['name'])],
                [
                    'user_id' => $admin->id,
                    'name' => $data['name'],
                    'address' => $data['address'],
                    'city' => 'Puerto Montt',
                    'region' => 'Los Lagos',
                    'country' => 'Chile',
                ]
            );

            // B) Crear/Buscar Profesor/a
            $teacher = Teacher::firstOrCreate(
                [
                    'email' => Str::slug($data['teacher']) . "@espaciocore.test",
                    'studio_id' => $studio->id
                ],
                [
                    'first_name' => $data['teacher'],
                    'is_active' => true,
                ]
            );

            // C) Crear Taller Base
            $workshop = Workshop::firstOrCreate(
                [
                    'name' => $data['workshop'], 
                    'studio_id' => $studio->id
                ],
                [
                    'teacher_id' => $teacher->id,
                    'color' => $data['color'],
                    'use_main_location' => true,
                    'target_audience' => 'adults',
                    'is_single_class' => false,
                ]
            );

            // C.2) Inyectar los Horarios Dinámicos al Taller
            foreach ($data['schedules'] as $schedule) {
                $workshop->schedules()->firstOrCreate([
                    'day_of_week' => $schedule['day'],
                    'start_time' => $schedule['time']
                ], [
                    'max_students' => $schedule['max_students']
                ]);
            }

            // D) Crear Alumnas para este estudio
            $seleccionadas = collect($alumnasBase)->random(5);
            foreach ($seleccionadas as $a) {
                Student::firstOrCreate(
                    [
                        'email' => Str::slug($a['first'] . $a['last']) . "@example.com",
                        'studio_id' => $studio->id
                    ],
                    [
                        'first_name' => $a['first'],
                        'last_name' => $a['last'],
                        'is_guest' => false,
                    ]
                );
            }
        }
    }
}