<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\ExploreController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\WorkshopController;
use App\Http\Controllers\TrainingMonthController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\StudioController; // Deberás crearlo para gestionar tus locales

/*
|--------------------------------------------------------------------------
| 1. RUTAS DE SUBDOMINIO (El Software de Gestión)
|--------------------------------------------------------------------------
*/
// Extraemos de forma segura solo el dominio limpio (ej: espaciocore.test) sin el http://
$baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'espaciocore.test';

Route::domain('{subdomain}.' . $baseDomain)->group(function () {
    
    Route::middleware(['auth', 'identify.studio'])->group(function () {
        
        // Dashboard del Estudio
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Módulo Alumnas
        Route::prefix('students')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('students.index');
            Route::post('/', [StudentController::class, 'store'])->name('students.store');
            Route::put('/{student}', [StudentController::class, 'update'])->name('students.update');
            Route::delete('/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
            Route::patch('/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
            Route::delete('/{id}/force', [StudentController::class, 'forceDelete'])->name('students.force_delete');
            Route::get('/{student}/calendar/{month?}', [StudentController::class, 'calendar'])->name('students.calendar');
        });

        // Módulo Talleres
        Route::resource('workshops', WorkshopController::class);

        // Módulo Entrenamientos (Planificación)
        Route::get('/entrenamientos', [TrainingMonthController::class, 'index'])->name('entrenamientos.index');
        Route::post('/entrenamientos', [TrainingMonthController::class, 'store'])->name('entrenamientos.store');
        Route::get('/entrenamientos/{month}', [TrainingMonthController::class, 'show'])->name('entrenamientos.show');
        Route::delete('/entrenamientos/{month}', [TrainingMonthController::class, 'destroyMonth'])->name('entrenamientos.destroyMonth');

        // Sesiones y Asistencias
        Route::get('/sessions/{session}', [ClassSessionController::class, 'show'])->name('sessions.show');
        Route::patch('/sessions/{session}/cancel', [ClassSessionController::class, 'cancel'])->name('sessions.cancel');
        Route::post('/sessions/{session}/infrequent', [ClassSessionController::class, 'storeInfrequent'])->name('sessions.infrequent');
        Route::post('/sessions/{session}/attendance/{student}', [AttendanceController::class, 'toggle'])->name('attendance.toggle');

        // Pagos
        Route::post('/students/{student}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('/api/students/{student}/available-sessions', [PaymentController::class, 'getAvailableSessions']);
    });
});

/*
|--------------------------------------------------------------------------
| 2. RUTAS CENTRALES (Landing, Login y Selección de Estudio)
|--------------------------------------------------------------------------
*/


// ANTES (Mala práctica en producción):
// Route::get('/', function () { return view('welcome'); });

// AHORA (Estándar Senior):
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/explorar', [ExploreController::class, 'index'])->name('explore');

Route::middleware('auth')->group(function () {
    // Selección y Creación de Estudios
    Route::get('/mis-estudios', [StudioController::class, 'index'])->name('studios.index');
    Route::post('/mis-estudios', [StudioController::class, 'store'])->name('studios.store');

    // Perfil General
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// OAuth
Route::middleware('guest')->group(function () {
    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
});

require __DIR__.'/auth.php';