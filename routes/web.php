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
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\Admin\StudioManagementController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\MercadoPagoOAuthController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentReturnController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SupportController;

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
Route::post('/api/webhooks/mercadopago', [\App\Http\Controllers\WebhookController::class, 'mercadopago'])->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])->name('webhooks.mp');

// Callbacks globales de MercadoPago para pago a profesores (dominio principal, sin subdominio)
Route::get('/api/payroll/mp/success', [\App\Http\Controllers\PayrollController::class, 'mpSuccessGlobal'])->name('payroll.mp.success.global');
Route::get('/api/payroll/mp/failure', [\App\Http\Controllers\PayrollController::class, 'mpFailureGlobal'])->name('payroll.mp.failure.global');

/*
|--------------------------------------------------------------------------
| 1. RUTAS DEL DOMINIO PRINCIPAL (estadoprisma.test)
|--------------------------------------------------------------------------
*/
Route::domain($baseDomain)->group(function () {
    
    // Landing Page y Explorar
    Route::get('/', [HomeController::class, 'index'])->name('home');
    Route::get('/explorar', [ExploreController::class, 'index'])->name('explore');

    // Endpoints AJAX para dropdowns dependientes del explorer (públicos, GET)
    Route::get('/api/explore/disciplines', [ExploreController::class, 'disciplinesByArea'])->name('api.explore.disciplines');
    Route::get('/api/explore/regions', [ExploreController::class, 'regionsByCountry'])->name('api.explore.regions');
    Route::get('/api/explore/cities', [ExploreController::class, 'citiesByRegion'])->name('api.explore.cities');

    // SEO: robots.txt y sitemap.xml
    Route::get('/robots.txt', [SeoController::class, 'robots']);
    Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);

    // Soporte público: consultas y agendamiento de demo
    Route::get('/soporte', [SupportController::class, 'create'])->name('support.create');
    Route::post('/soporte', [SupportController::class, 'store'])->name('support.store');

    // Páginas legales (Ley 21.719 — Protección de Datos Chile)
    Route::view('/terminos-y-condiciones', 'legal.terms')->name('legal.terms');
    Route::view('/politica-de-privacidad', 'legal.privacy')->name('legal.privacy');

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

    // Decisión de dependiente: requiere perfil completo (national_id + country_id)
    Route::middleware(['auth', 'check.profile', 'dependent.decision'])->group(function () {
        Route::get('/profile/dependiente/decision', [FamilyController::class, 'dependentDecision'])->name('profile.dependent.decision');
        Route::post('/profile/dependiente/unlink', [FamilyController::class, 'unlinkDependent'])->name('profile.dependent.unlink');
        Route::post('/profile/dependiente/share', [FamilyController::class, 'shareAndKeepDependent'])->name('profile.dependent.share');
    });

    Route::middleware(['auth', 'check.profile', 'dependent.decision'])->group(function () {
        
        // El botón de la UI apuntará aquí
        Route::get('/oauth/mercadopago/redirect', [MercadoPagoOAuthController::class, 'redirect'])
            ->name('mp.oauth.redirect');
            
        // Mercado Pago nos devolverá aquí
        Route::get('/oauth/mercadopago/callback', [MercadoPagoOAuthController::class, 'callback'])
            ->name('mp.oauth.callback');

        // OAuth Mercado Pago para Profesores (vincula al User global)
        Route::get('/oauth/mercadopago/profesor/redirect', [MercadoPagoOAuthController::class, 'redirect'])
            ->defaults('source', 'teacher')
            ->name('mp.oauth.teacher.redirect');

        // Carrito / Reservas
        Route::get('/mis-reservas', [App\Http\Controllers\Global\CartController::class, 'index'])->name('cart.index');
        Route::post('/api/cart/calculate', [App\Http\Controllers\Global\CartController::class, 'calculate'])->name('api.cart.calculate');
        Route::post('/api/cart/guest-sessions', [App\Http\Controllers\Global\CartController::class, 'getGuestSessions']);

        // --- CHECKOUT DE MERCADO PAGO AQUÍ ---
        Route::post('/pagos/generar-checkout', [CheckoutController::class, 'generarCheckout'])->name('checkout.generate');

        Route::get('/pagos/exito', [PaymentReturnController::class, 'success'])->name('payments.success');
        Route::get('/pagos/pendiente', [PaymentReturnController::class, 'pending'])->name('payments.pending');
        Route::get('/pagos/error', [PaymentReturnController::class, 'failure'])->name('payments.failure');

        // Selección y Creación de Estudios (Lobby)
        Route::get('/mis-estudios', [StudioController::class, 'index'])->name('studios.index');
        Route::post('/mis-estudios', [StudioController::class, 'store'])->name('studios.store');
        Route::put('/studios/{studio}', [StudioController::class, 'update'])->name('studios.update');
        Route::delete('/studios/{studio}', [StudioController::class, 'destroy'])->name('studios.destroy');
    Route::post('/studios/{studio}/early-close', [StudioController::class, 'earlyCloseCheckout'])->name('studios.early-close');
    
        // Suscripción a Planes (Mercado Pago) - SE AGREGA 'verified' SOLO AQUÍ
        Route::post('/studios/{studio}/subscribe', [SubscriptionController::class, 'subscribe'])
            ->middleware('verified')
            ->name('studios.subscribe');

        // 👇 NUEVA RUTA PARA REINTENTAR PAGO (Movida al dominio principal)
        Route::post('/studios/{studio}/retry-payment', [SubscriptionController::class, 'retryPayment'])
            ->middleware('verified')
            ->name('studios.retry-payment');

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
        Route::put('/profile/familia/{dependent}', [FamilyController::class, 'update'])->name('profile.family.update');
        Route::delete('/profile/familia/{dependent}', [FamilyController::class, 'destroy'])->name('profile.family.destroy');
        Route::delete('/profile/familia/salir/{dependent}', [\App\Http\Controllers\Global\FamilyController::class, 'leaveFamily'])->name('profile.family.leave');
        Route::post('/profile/familia/{dependent}/accept', [\App\Http\Controllers\Global\FamilyController::class, 'acceptMembership'])->name('profile.family.accept-membership');
        Route::delete('/profile/familia/{dependent}/reject', [\App\Http\Controllers\Global\FamilyController::class, 'rejectMembership'])->name('profile.family.reject-membership');

        // Historial de Pagos del Usuario
        Route::get('/mis-pagos', [\App\Http\Controllers\Global\PaymentHistoryController::class, 'index'])->name('global.payments.index');
        Route::delete('/mis-pagos/mercadopago', [\App\Http\Controllers\Global\PaymentHistoryController::class, 'disconnectMercadoPago'])->name('global.payments.mp.disconnect');
        
        Route::post('/api/student/enroll-toggle', [UserClassController::class, 'toggleEnrollment'])->name('global.student.enroll.toggle');

    });

    // Rutas firmadas para aceptar/rechazar vínculo familiar
    // (NO requieren dependent.decision — el usuario objetivo debe poder aceptar sin resolver su decisión)
    Route::middleware(['auth', 'signed'])->group(function () {
        Route::get('/profile/familia/{dependent}/accept', [FamilyController::class, 'acceptLink'])
            ->name('profile.family.accept');
        Route::get('/profile/familia/{dependent}/reject', [FamilyController::class, 'rejectLink'])
            ->name('profile.family.reject');
    });

    // Panel de Super Administrador (Backoffice)
    Route::middleware(['auth', 'super.admin'])->prefix('admin')->group(function () {
        Route::get('/estudios', [StudioManagementController::class, 'index'])
            ->name('admin.studios.index');
        Route::patch('/estudios/{studio}/plan', [StudioManagementController::class, 'updatePlan'])
                ->name('admin.studios.update-plan');
        // --- NUEVAS RUTAS PARA PLANES ---
        Route::get('/planes', [SubscriptionPlanController::class, 'index'])
            ->name('admin.plans.index');
        Route::post('/planes', [SubscriptionPlanController::class, 'store'])
            ->name('admin.plans.store');
        Route::put('/planes/{plan}', [SubscriptionPlanController::class, 'update'])
            ->name('admin.plans.update');

        // Para activar/desactivar en lugar de borrar (Soft-disable)
        Route::patch('/planes/{plan}/toggle', [SubscriptionPlanController::class, 'toggle'])
            ->name('admin.plans.toggle');
        Route::get('/estudios/{studio}/auditoria', [\App\Http\Controllers\Admin\StudioManagementController::class, 'audit'])
            ->name('admin.studios.audit');

        // --- GESTIÓN DE FACTURACIÓN (Piso Mínimo y Facturas) ---
        Route::get('/billing', [BillingController::class, 'index'])
            ->name('admin.billing.index');
        Route::patch('/billing/standard-floor', [BillingController::class, 'updateStandardFloor'])
            ->name('admin.billing.update-standard-floor');
        Route::patch('/billing/invoices/{invoice}/floor', [BillingController::class, 'updateInvoiceFloor'])
            ->name('admin.billing.update-invoice-floor');
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
    // Estas rutas deben ser accesibles sin estar logueado.
    // El middleware public.studio.status bloquea el escaparate si el
    // estudio tiene facturas vencidas (HTTP 503 + vista de suspensión).
    Route::middleware(['public.studio.status'])->group(function () {
        // Escaparate público del estudio — vistas separadas por sección
        Route::get('/', [StudioPublicController::class, 'perfil'])->name('studio.public.perfil');
        Route::get('/talleres', [StudioPublicController::class, 'talleres'])->name('studio.public.talleres');
        Route::get('/promos', [StudioPublicController::class, 'promos'])->name('studio.public.promos');
        Route::get('/calendario', [StudioPublicController::class, 'calendario'])->name('studio.public.calendario');
    });

    // --- GESTIÓN INTERNA DEL ESTUDIO (Requiere Auth e Identificar Estudio) ---
    Route::middleware(['web', 'auth', 'identify.studio', 'check.studio.debt'])->group(function () {

        Route::get('/cuenta', [AccountController::class, 'index'])->name('account.index');
        Route::get('/cuenta/facturacion', [AccountController::class, 'billing'])->name('account.billing');
        Route::post('/cuenta/facturacion/{invoiceId}/pagar', [AccountController::class, 'payInvoice'])->name('account.billing.pay');
        Route::delete('/cuenta/mercadopago', [AccountController::class, 'disconnectMercadoPago'])->name('account.mp.disconnect');
        Route::post('/cuenta/mercadopago/setup-static-qr', [AccountController::class, 'setupStaticQR'])->name('account.mp.setup-static-qr');
        
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
            Route::get('/{student}/payments', [StudentController::class, 'payments'])->name('students.payments');
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
        Route::delete('/trainingmonth/bulk-destroy', [TrainingMonthController::class, 'bulkDestroy'])->name('trainingmonth.bulk_destroy');

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

        // Gateway de pago (API desde el calendario)
        Route::prefix('api/students/{student}/gateway')->name('gateway.')->group(function () {
            Route::get('/calculate', [PaymentController::class, 'calculateGatewayPrice'])->name('calculate');
            Route::post('/static-qr', [PaymentController::class, 'generateStaticQROrder'])->name('static-qr');
            Route::post('/dynamic-qr', [PaymentController::class, 'generateDynamicQR'])->name('dynamic-qr');
            Route::post('/send-email', [PaymentController::class, 'sendPaymentEmail'])->name('send-email');
        });

        // Suscripción SaaS del Estudio — DESHABILITADA (sistema migrado a Facturación por Uso)
        // Las rutas antiguas de suscripción se eliminan en esta refactorización.
        // Route::get('/suscripcion', ...);
        // Route::post('/studios/{studio}/subscribe', ...);
        // Route::post('/studios/{studio}/retry-payment', ...);

        // Módulo de Liquidaciones (Payroll) - Anidado por Profesor
        Route::get('/teachers/{teacher}/payroll/{month?}', [\App\Http\Controllers\PayrollController::class, 'show'])->name('teachers.payroll.show');
        Route::post('/teachers/{teacher}/payroll', [\App\Http\Controllers\PayrollController::class, 'store'])->name('teachers.payroll.store');
        Route::get('/teachers/{teacher}/payroll/mp/success', [\App\Http\Controllers\PayrollController::class, 'mpSuccess'])->name('teachers.payroll.mp.success');
        Route::get('/teachers/{teacher}/payroll/mp/failure', [\App\Http\Controllers\PayrollController::class, 'mpFailure'])->name('teachers.payroll.mp.failure');
        Route::get('/teachers/{teacher}/payroll/{payment}/resume', [\App\Http\Controllers\PayrollController::class, 'resume'])->name('teachers.payroll.resume');
        Route::delete('/teachers/{teacher}/payroll/{payment}', [\App\Http\Controllers\PayrollController::class, 'destroy'])->name('teachers.payroll.destroy');
    });
});