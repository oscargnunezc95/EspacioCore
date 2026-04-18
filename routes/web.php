<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TrainingMonthController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\WorkshopController;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Asistencias y Clases
Route::post('/sessions/{session}/attendance/{student}', [AttendanceController::class, 'toggle'])->name('attendance.toggle');
Route::get('/sessions/{session}', [ClassSessionController::class, 'show'])->name('sessions.show');
Route::patch('/sessions/{session}/cancel', [ClassSessionController::class, 'cancel'])->name('sessions.cancel');
// NUEVA RUTA: Invitadas de Clase Única
Route::post('/sessions/{session}/guest', [ClassSessionController::class, 'storeGuest'])->name('sessions.guest');

// Pagos
Route::get('/students/{student}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/students/{student}/payments', [PaymentController::class, 'store'])->name('payments.store');
Route::get('/api/students/{student}/available-sessions', [PaymentController::class, 'getAvailableSessions']);
Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

// Alumnas (Directorio y Calendario)
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
Route::patch('/students/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
Route::delete('/students/{id}/force-delete', [StudentController::class, 'forceDelete'])->name('students.force_delete');
Route::get('/students/{student}/calendar/{month?}', [StudentController::class, 'calendar'])->name('students.calendar');

// Talleres (Configuración)
Route::get('/classes', [WorkshopController::class, 'index'])->name('workshops.index');
Route::post('/workshops', [WorkshopController::class, 'store'])->name('workshops.store');
Route::put('/workshops/{workshop}', [WorkshopController::class, 'update'])->name('workshops.update');
Route::delete('/workshops/{workshop}', [WorkshopController::class, 'destroy'])->name('workshops.destroy');

// Meses y Entrenamientos
Route::get('/entrenamientos', [TrainingMonthController::class, 'index'])->name('entrenamientos.index');
Route::post('/entrenamientos/generar', [TrainingMonthController::class, 'store'])->name('entrenamientos.store');
Route::get('/entrenamientos/mes/{monthId}', [TrainingMonthController::class, 'show'])->name('entrenamientos.show');
Route::delete('/entrenamientos/mes/{monthId}', [TrainingMonthController::class, 'destroyMonth'])->name('entrenamientos.destroyMonth');

Route::post('/sessions/{session}/infrequent', [ClassSessionController::class, 'storeInfrequent'])->name('sessions.infrequent');