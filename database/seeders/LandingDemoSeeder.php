<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Studio;
use App\Models\Country;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\Workshop;
use App\Models\Area;
use App\Models\Discipline;
use App\Models\ClassSession;

class LandingDemoSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Prerrequisitos de Arquitectura (País y Owner)
        $country = Country::firstOrCreate(
            ['code' => 'CL'],
            ['name' => 'Chile', 'tax_id_label' => 'RUT', 'currency_code' => 'CLP', 'currency_symbol' => '$']
        );

        $adminUser = User::firstOrCreate(
            ['email' => 'oscargnunezc18@gmail.com'],
            [
                'name' => 'Luis Nuñez',
                'password' => Hash::make('qwerqwer'),
                'email_verified_at' => now(),
                'national_id' => '18802106-9',
                'country_id' => 1,
            ]
        );

        // 2. Creación del Estudio
        $studio = Studio::firstOrCreate(
            ['subdomain' => 'estadoprisma'],
            [
                'user_id' => $adminUser->id,
                'country_id' => $country->id,
                'name' => 'Estado Prisma',
                'city' => 'Santiago',
            ]
        );

        // 3. Disciplinas y Talleres
        $area = Area::firstOrCreate(['name' => 'Artes Escénicas']);
        $discipline = Discipline::firstOrCreate(['area_id' => $area->id, 'name' => 'Acrobacia Aérea']);

        // Profesora
        $teacherUser = User::firstOrCreate(
            ['email' => 'profe.laura@estadoprisma.cl'],
            ['name' => 'Laura Valenzuela', 'national_id' => '15222333-4', 'country_id' => $country->id, 'password' => Hash::make('password')]
        );

        $teacher = Teacher::firstOrCreate(
            ['national_id' => '15222333-4'],
            [
                'studio_id' => $studio->id,
                'user_id' => $teacherUser->id,
                'country_id' => $country->id,
                'first_name' => 'Laura',
                'last_name' => 'Valenzuela',
                'email' => 'profe.laura@estadoprisma.cl',
            ]
        );

        // Talleres
        $workshops = [
            Workshop::firstOrCreate(['name' => 'Acrobacia en Tela Inicial', 'studio_id' => $studio->id], ['teacher_id' => $teacher->id, 'discipline_id' => $discipline->id, 'color' => 'rose']),
            Workshop::firstOrCreate(['name' => 'Danza Contemporánea', 'studio_id' => $studio->id], ['teacher_id' => $teacher->id, 'discipline_id' => $discipline->id, 'color' => 'purple']),
            Workshop::firstOrCreate(['name' => 'Flexibilidad Avanzada', 'studio_id' => $studio->id], ['teacher_id' => $teacher->id, 'discipline_id' => $discipline->id, 'color' => 'emerald']),
        ];

        // 4. Creación de 10 Alumnas
        $nombresAlumnas = [
            'Valentina Rojas', 'Camila Soto', 'Isidora Muñoz', 'Sofía Castro', 'Martina Silva', 
            'Catalina Vargas', 'Antonia Morales', 'Florencia Díaz', 'Javiera Torres', 'Paula Reyes'
        ];

        $students = [];
        foreach ($nombresAlumnas as $index => $nombreCompleto) {
            $nombres = explode(' ', $nombreCompleto);
            $rut = '20' . str_pad($index, 6, '0', STR_PAD_LEFT) . '-K';
            
            $user = User::firstOrCreate(
                ['email' => strtolower($nombres[0] . '.' . $nombres[1] . '@ejemplo.cl')],
                ['name' => $nombreCompleto, 'national_id' => $rut, 'country_id' => $country->id, 'password' => Hash::make('password')]
            );

            $students[] = Student::firstOrCreate(
                ['national_id' => $rut, 'studio_id' => $studio->id],
                [
                    'user_id' => $user->id,
                    'country_id' => $country->id,
                    'first_name' => $nombres[0],
                    'last_name' => $nombres[1],
                    'email' => $user->email,
                ]
            );
        }

        // 5. Generación de Pagos (Abril, Mayo, Junio y Mes Actual)
        $meses = [
            'Abril' => Carbon::create(2026, 4, 1),
            'Mayo'  => Carbon::create(2026, 5, 1),
            'Junio' => Carbon::create(2026, 6, 1),
            'Julio' => Carbon::now()->startOfMonth(), // Mes actual incluido
        ];

        $metodosPago = ['mercadopago', 'transferencia', 'efectivo'];

        foreach ($meses as $nombreMes => $fechaBase) {
            $esMesActual = $fechaBase->month === Carbon::now()->month;

            // A. Pagos de Alumnas: 5 pagos por cada alumna en este mes
            foreach ($students as $student) {
                for ($i = 0; $i < 5; $i++) {
                    $workshop = $workshops[array_rand($workshops)];
                    
                    // Si es el mes actual, restringimos la fecha aleatoria para que no sea en el futuro
                    $maxDays = $esMesActual ? Carbon::now()->day : $fechaBase->daysInMonth;
                    $paymentDate = $fechaBase->copy()->addDays(rand(0, max(0, $maxDays - 1)))->setTime(rand(9, 19), rand(0, 59));

                    DB::table('payments')->insert([
                        'studio_id'      => $studio->id,
                        'student_id'     => $student->id,
                        'workshop_id'    => $workshop->id,
                        'payment_type'   => 'mensualidad',
                        'payment_method' => $metodosPago[array_rand($metodosPago)], // Selección de los 3 métodos
                        'amount'         => rand(25000, 45000),
                        'status'         => 'approved',
                        'created_at'     => $paymentDate,
                        'updated_at'     => $paymentDate,
                    ]);
                }
            }

            // B. Pago al Profesor: 1 pago en este mes
            $teacherPaymentDate = $esMesActual ? Carbon::now() : $fechaBase->copy()->endOfMonth()->subDays(rand(0, 3));
            DB::table('teacher_payments')->insert([
                'studio_id'      => $studio->id,
                'teacher_id'     => $teacher->id,
                'month_year'     => $fechaBase->format('Y-m'),
                'amount'         => 350000,
                // CORRECCIÓN: Respetamos el ENUM('manual', 'mercadopago') de la base de datos
                'payment_method' => rand(1, 10) > 5 ? 'mercadopago' : 'manual', 
                'status'         => 'paid',
                'created_at'     => $teacherPaymentDate,
                'updated_at'     => $teacherPaymentDate,
            ]);
        }

        // 6. Generación de Clases y Morosidad para el apartado "Lo del Día"
        $clasesHoy = [];
        for ($i = 0; $i < 2; $i++) { // Generamos 2 clases para hoy
            $clasesHoy[] = ClassSession::firstOrCreate(
                [
                    'studio_id' => $studio->id,
                    'workshop_id' => $workshops[$i]->id,
                    'date' => Carbon::today()->toDateString(),
                ],
                [
                    'start_time' => Carbon::now()->addHours($i + 1)->format('H:00:00'),
                    'is_cancelled' => false
                ]
            );
        }

        // Inscribimos alumnas en las clases de hoy (algunas al día, otras morosas)
        foreach ($clasesHoy as $clase) {
            $asistentes = array_rand($students, 3); // Elegimos 3 alumnas al azar
            
            foreach ($asistentes as $idx => $studentKey) {
                $estudiante = $students[$studentKey];
                
                // Forzamos a la primera alumna a estar morosa ('pending') para poblar la tarjeta "Clases Impagas"
                $statusPago = ($idx === 0) ? 'pending' : 'paid';

                DB::table('class_session_student')->insertOrIgnore([
                    'class_session_id' => $clase->id,
                    'student_id' => $estudiante->id,
                    'payment_status' => $statusPago,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);

                // Insertamos la asistencia para que la query de morosas la detecte
                DB::table('attendances')->insertOrIgnore([
                    'studio_id' => $studio->id,
                    'class_session_id' => $clase->id,
                    'student_id' => $estudiante->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }
        }

        $this->command->info('✅ Datos de marketing inyectados: Pagos (3 métodos) distribuidos hasta el mes actual y clases programadas para el día de hoy.');
    }
}