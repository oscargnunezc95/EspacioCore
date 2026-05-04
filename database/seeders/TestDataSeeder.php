<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use App\Models\Workshop;
use App\Models\ClassSession;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Talleres de prueba
        $telas = Workshop::create([
            'name' => 'Telas Acrobáticas',
            'color' => 'purple',
            'trainer' => 'María José',
            'start_time' => '18:00',
            'repeat_day' => 1, // Lunes
            'is_single_class' => false
        ]);

        $yoga = Workshop::create([
            'name' => 'Yoga Integral',
            'color' => 'emerald',
            'trainer' => 'Paz Aravena',
            'start_time' => '09:00',
            'repeat_day' => 3, // Miércoles
            'is_single_class' => false
        ]);

        $masterclass = Workshop::create([
            'name' => 'Masterclass Flexibilidad',
            'color' => 'amber',
            'trainer' => 'Invitado Especial',
            'start_time' => '20:00',
            'specific_date' => Carbon::now()->format('Y-m-d'),
            'is_single_class' => true
        ]);

        // 2. Crear 10 alumnas/os
        $nombres = [
            'Valentina Rojas', 'Isidora Silva', 'Florencia Araya', 
            'Antonia López', 'Martina Castro', 'Francisca Reyes',
            'Josefa Morales', 'Fernanda Tapia', 'Camila Soto', ' Javiera Muñoz'
        ];

        $students = collect();
        foreach ($nombres as $index => $nombre) {
            $students->push(Student::create([
                'rut' => (18000000 + $index) . '-' . ($index % 9),
                'name' => $nombre,
                'phone' => '+569' . (90000000 + $index),
                'is_guest' => false
            ]));
        }

        // 3. Generar Sesiones para el mes actual
        $start = Carbon::now()->startOfMonth();
        $end = Carbon::now()->endOfMonth();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            if ($date->dayOfWeekIso == $telas->repeat_day) {
                ClassSession::create([
                    'workshop_id' => $telas->id,
                    'date' => $date->toDateString(),
                    'start_time' => $telas->start_time
                ]);
            }
            if ($date->dayOfWeekIso == $yoga->repeat_day) {
                ClassSession::create([
                    'workshop_id' => $yoga->id,
                    'date' => $date->toDateString(),
                    'start_time' => $yoga->start_time
                ]);
            }
        }

        // Crear la sesión de la Masterclass
        $masterSession = ClassSession::create([
            'workshop_id' => $masterclass->id,
            'date' => $masterclass->specific_date,
            'start_time' => $masterclass->start_time
        ]);

        // 4. Simular Asistencias y Pagos
        $allSessions = ClassSession::all();

        foreach ($students as $student) {
            // Tomamos 3 sesiones al azar para cada alumna
            $randomSessions = $allSessions->random(3);

            foreach ($randomSessions as $index => $session) {
                // Registrar Asistencia
                $session->attendances()->create(['student_id' => $student->id]);

                // Solo pagamos las 2 primeras sesiones (para dejar la 3ra como DEUDA)
                if ($index < 2) {
                    $payment = Payment::create([
                        'student_id' => $student->id,
                        'workshop_id' => $session->workshop_id,
                        'amount' => 15000,
                        'payment_type' => 'single',
                        'receipt_path' => null // Sin foto para el mock
                    ]);

                    // Amarrar el pago a la sesión
                    $payment->classSessions()->attach($session->id, ['student_id' => $student->id]);
                }
            }
        }
    }
}