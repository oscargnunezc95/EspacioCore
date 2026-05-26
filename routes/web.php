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
use App\Http\Controllers\StudioPublicController;
use App\Http\Controllers\Global\UserClassController;
use App\Http\Controllers\Global\FamilyController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\MercadoPagoOAuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentReturnController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SeoController;

$baseDomain = parse_url(config('app.url'), PHP_URL_HOST) ?: 'estadoprisma.test';

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/global/student/enroll/bulk', [\App\Http\Controllers\Global\UserClassController::class, 'bulkEnroll'])
        ->name('global.student.enroll.bulk');
    Route::post('/notificaciones/{id}/leer', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notificaciones/leer-todas', [NotificationController::class, 'markAllAsRead'])->name('notifications.read.all');
});

// ========================================================
// RUTA DE WEBHOOKS (Abierta para cualquier dominio/túnel)
// ========================================================
Route::post('/api/webhooks/mercadopago', [\App\Http\Controllers\WebhookController::class, 'mercadopago'])->name('webhooks.mp');

/*
|--------------------------------------------------------------------------
| 1. RUTAS DEL DOMINIO PRINCIPAL (estadoprisma.test)
|--------------------------------------------------------------------------
*/
Route::domain($baseDomain)->group(function () {
    
    // Landing Page y Explorar
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/explorar', [ExploreController::class, 'index'])->name('explore');

    // SEO: robots.txt y sitemap.xml
    Route::get('/robots.txt', [SeoController::class, 'robots']);
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);

    // ---------------------------------------------------------
    // RUTAS PÚBLICAS / INVITADOS (OAuth Google y Completar)
    // ---------------------------------------------------------
    Route::middleware('guest')->group(function () {
        Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
        Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
        
        // Completar perfil tras login social
        Route::get('/auth/google/complete', [GoogleController::class, 'completeProfile'])->name('auth.google.complete');
        Route::post('/auth/google/complete', [GoogleController::class, 'storeCompleteProfile'])->name('auth.google.store');
    });

    // ---------------------------------------------------------
    // RUTAS AUTENTICADAS (Lobby y Gestión Global del Usuario)
    // ---------------------------------------------------------
    Route::middleware(['auth', 'verified', 'check.profile'])->group(function () {
        
        // El botón de la UI apuntará aquí
        Route::get('/oauth/mercadopago/redirect', [MercadoPagoOAuthController::class, 'redirect'])
            ->name('mp.oauth.redirect');
            
        // Mercado Pago nos devolverá aquí
        Route::get('/oauth/mercadopago/callback', [MercadoPagoOAuthController::class, 'callback'])
            ->name('mp.oauth.callback');

        // Carrito / Reservas
        Route::get('/mis-reservas', [App\Http\Controllers\Global\CartController::class, 'index'])->name('cart.index');
        Route::post('/api/cart/calculate', [App\Http\Controllers\Global\CartController::class, 'calculate'])->name('api.cart.calculate');
        Route::post('/api/cart/guest-sessions', [App\Http\Controllers\Global\CartController::class, 'getGuestSessions']);

        // --- CHECKOUT DE MERCADO PAGO AQUÍ ---
        Route::post('/pagos/generar-checkout', [CheckoutController::class, 'generarCheckout'])->name('checkout.generate');

        // Selección y Creación de Estudios (Lobby)
        Route::get('/mis-estudios', [StudioController::class, 'index'])->name('studios.index');
        Route::post('/mis-estudios', [StudioController::class, 'store'])->name('studios.store');
        Route::put('/studios/{studio}', [StudioController::class, 'update'])->name('studios.update');

        // Suscripción a Planes (Mercado Pago)
        Route::post('/studios/{studio}/subscribe', [SubscriptionController::class, 'subscribe'])
            ->name('studios.subscribe');

        // Clases del Usuario (Portales)
        Route::get('/mis-clases/alumno', [UserClassController::class, 'asStudent'])->name('global.classes.student');
        Route::get('/mis-clases/profesor', [UserClassController::class, 'asTeacher'])->name('global.classes.teacher');
        Route::get('/mis-clases/profesor/calendario/{month}', [UserClassController::class, 'teacherCalendar'])->name('global.classes.teacher.calendar');
        Route::get('/mis-clases/profesor/sesion/{session}', [UserClassController::class, 'teacherSession'])->name('global.classes.teacher.session');

        // Perfil General
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); 
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
        
        //gestión de miembros familiares (apoderados, hijos, etc.)
        Route::get('/profile/familia', [FamilyController::class, 'index'])->name('profile.family.index');
        Route::post('/profile/familia', [FamilyController::class, 'store'])->name('profile.family.store');
        Route::put('/profile/familia/{dependent}', [FamilyController::class, 'update'])->name('profile.family.update'); // 👈 Esta es la nueva
        Route::delete('/profile/familia/{dependent}', [FamilyController::class, 'destroy'])->name('profile.family.destroy');

        // Historial de Pagos del Usuario
        Route::get('/mis-pagos', [\App\Http\Controllers\Global\PaymentHistoryController::class, 'index'])->name('global.payments.index');
        
        Route::post('/api/student/enroll-toggle', [UserClassController::class, 'toggleEnrollment'])->name('global.student.enroll.toggle');

    });

    require __DIR__.'/auth.php';
});

/*
|--------------------------------------------------------------------------
| 2. RUTAS DE SUBDOMINIO (Escaparate Público + Software de Gestión)
|--------------------------------------------------------------------------
*/
Route::domain('{subdomain}.' . $baseDomain)->group(function () {
    
    // --- ACCESO PÚBLICO AL ESTUDIO (Link in Bio / Instagram) ---
    // Esta ruta debe ser accesible sin estar logueado
    Route::get('/', [StudioPublicController::class, 'show'])->name('studio.public.show');
    
    Route::get('/pagos/exito', [PaymentReturnController::class, 'success'])->name('payments.success');
    Route::get('/pagos/pendiente', [PaymentReturnController::class, 'pending'])->name('payments.pending');
    Route::get('/pagos/error', [PaymentReturnController::class, 'failure'])->name('payments.failure');

    // --- GESTIÓN INTERNA DEL ESTUDIO (Requiere Auth e Identificar Estudio) ---
    Route::middleware(['web', 'auth', 'verified', 'identify.studio'])->group(function () {
        
        Route::get('/cuenta', [AccountController::class, 'index'])->name('account.index');
        Route::delete('/cuenta/mercadopago', [AccountController::class, 'disconnectMercadoPago'])->name('account.mp.disconnect');
        
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
        Route::patch('teachers/{id}/restore', [TeacherController::class, 'restore'])->name('teachers.restore');
        Route::delete('teachers/{id}/force', [TeacherController::class, 'forceDelete'])->name('teachers.force_delete');

        // Módulo Talleres
        Route::resource('workshops', WorkshopController::class);

        // Módulo Promociones
        Route::resource('promotions', PromotionController::class)->except(['create', 'show', 'edit']);

        // Módulo Planificación (Ciclos Mensuales)
        Route::get('/trainingmonth', [TrainingMonthController::class, 'index'])->name('trainingmonth.index');
        Route::post('/trainingmonth', [TrainingMonthController::class, 'store'])->name('trainingmonth.store');
        Route::get('/trainingmonth/{month}', [TrainingMonthController::class, 'show'])->name('trainingmonth.show');

        // Sesiones y Asistencias
        Route::get('/sessions/{session}', [ClassSessionController::class, 'show'])->name('sessions.show');
        Route::put('/sessions/{session}', [ClassSessionController::class, 'update'])->name('sessions.update');
        Route::patch('/sessions/{session}/cancel', [ClassSessionController::class, 'cancel'])->name('sessions.cancel');
        Route::post('/sessions/{session}/enroll', [ClassSessionController::class, 'enrollStudent'])->name('sessions.enroll');
        Route::post('/sessions/{session}/attendance/{student}', [AttendanceController::class, 'toggle'])->name('attendance.toggle');

        // Pagos internos del estudio
        Route::post('/students/{student}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('/api/students/{student}/available-sessions', [PaymentController::class, 'getAvailableSessions']);
    });
});