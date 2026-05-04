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
use App\Http\Controllers\StudioController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\Global\UserClassController;
use App\Http\Controllers\Global\UserDebtController;

// Extraemos de forma segura solo el dominio limpio (ej: espaciocore.test) sin el http://
$baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'espaciocore.test';

/*
|--------------------------------------------------------------------------
| 1. RUTAS CENTRALES (Landing, Login y Selección de Estudio)
|--------------------------------------------------------------------------
| Al envolver estas rutas en el dominio base estricto, evitamos que los 
| subdominios "contaminen" las URL de Login, Registro o Logout.
*/
Route::domain($baseDomain)->group(function () {
    
    // Landing Page y Explorar
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/explorar', [ExploreController::class, 'index'])->name('explore');

    // Se exige correo confirmado en el Lobby ('verified')
    Route::middleware(['auth', 'verified'])->group(function () {
        // Selección y Creación de Estudios (Lobby)
        Route::get('/mis-estudios', [StudioController::class, 'index'])->name('studios.index');
        Route::post('/mis-estudios', [StudioController::class, 'store'])->name('studios.store');

        Route::get('/mis-clases/profesor', [UserClassController::class, 'asTeacher'])->name('global.classes.teacher');
        Route::get('/mis-clases/profesor/calendario/{month}', [UserClassController::class, 'teacherCalendar'])->name('global.classes.teacher.calendar');
        Route::get('/mis-clases/profesor/sesion/{session}', [UserClassController::class, 'teacherSession'])->name('global.classes.teacher.session');
        
        Route::get('/mis-clases/alumno', [UserClassController::class, 'asStudent'])->name('global.classes.student');
        Route::get('/mis-deudas', [UserDebtController::class, 'index'])->name('global.debts.index');

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

    // ¡CRÍTICO! Rutas de Autenticación ancladas al dominio central
    require __DIR__.'/auth.php';
});


/*
|--------------------------------------------------------------------------
| 2. RUTAS DE SUBDOMINIO (El Software de Gestión)
|--------------------------------------------------------------------------
| Todas las operaciones internas del negocio van aquí. Exigen que la URL 
| tenga el subdominio y el middleware extraiga el ID correcto.
*/
Route::domain('{subdomain}.' . $baseDomain)->group(function () {
    
    // Ecosistema interno del estudio
    Route::middleware(['web', 'auth', 'verified', 'identify.studio'])->group(function () {
        
        // Dashboard del Estudio
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Módulo alumnas/os
        Route::prefix('students')->group(function () {
            Route::get('/', [StudentController::class, 'index'])->name('students.index');
            Route::post('/', [StudentController::class, 'store'])->name('students.store');
            Route::put('/{student}', [StudentController::class, 'update'])->name('students.update');
            Route::delete('/{student}', [StudentController::class, 'destroy'])->name('students.destroy');
            Route::patch('/{id}/restore', [StudentController::class, 'restore'])->name('students.restore');
            Route::delete('/{id}/force', [StudentController::class, 'forceDelete'])->name('students.force_delete');
            Route::get('/{student}/calendar/{month?}', [StudentController::class, 'calendar'])->name('students.calendar');
        });
        
        // Módulo profesores
        Route::resource('teachers', TeacherController::class)->except(['create', 'show', 'edit']);

        // Módulo Talleres (Eliminadas las rutas de inscripción global al taller)
        Route::resource('workshops', WorkshopController::class);

        // Módulo Promociones
        Route::resource('promotions', PromotionController::class)->except(['create', 'show', 'edit']);

        // Módulo Entrenamientos (Planificación)
        Route::get('/entrenamientos', [TrainingMonthController::class, 'index'])->name('entrenamientos.index');
        Route::post('/entrenamientos', [TrainingMonthController::class, 'store'])->name('entrenamientos.store');
        Route::get('/entrenamientos/{month}', [TrainingMonthController::class, 'show'])->name('entrenamientos.show');
        Route::delete('/entrenamientos/{month}', [TrainingMonthController::class, 'destroyMonth'])->name('entrenamientos.destroyMonth');

        // Sesiones y Asistencias (Nueva ruta de Drop-in/Inscripción individual)
        Route::get('/sessions/{session}', [ClassSessionController::class, 'show'])->name('sessions.show');
        Route::patch('/sessions/{session}/cancel', [ClassSessionController::class, 'cancel'])->name('sessions.cancel');
        Route::post('/sessions/{session}/enroll', [ClassSessionController::class, 'enrollStudent'])->name('sessions.enroll');
        Route::post('/sessions/{session}/attendance/{student}', [AttendanceController::class, 'toggle'])->name('attendance.toggle');

        // Pagos
        Route::post('/students/{student}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('/api/students/{student}/available-sessions', [PaymentController::class, 'getAvailableSessions']);
    });
});