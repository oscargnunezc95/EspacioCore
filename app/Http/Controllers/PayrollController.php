<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Models\Teacher;
use App\Models\TeacherPayment;
use App\Models\Studio;
use App\Services\PayrollService;
use App\Services\MercadoPagoService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PayrollController extends Controller
{
    protected $payrollService;
    protected $mpService;

    public function __construct(PayrollService $payrollService, MercadoPagoService $mpService)
    {
        $this->payrollService = $payrollService;
        $this->mpService = $mpService;
    }

    /**
     * Muestra el historial de liquidación del mes para un profesor específico.
     * Route Model Binding inyecta Teacher $teacher automáticamente.
     * Si el profesor no pertenece al estudio actual, el Global Scope lo bloquea (404).
     */
    public function show(Request $request, $subdomain, Teacher $teacher, $month = null)
    {
        // Auditoría de seguridad explícita (defensa en profundidad)
        $studioId = Config::get('tenant.studio_id');
        if ($teacher->studio_id !== (int) $studioId) {
            abort(403, 'Este profesor no pertenece a tu estudio.');
        }

        $monthYear = $month ?? now()->format('Y-m');
        $studio = Studio::findOrFail($studioId);

        $report = $this->payrollService->getMonthlyReport($teacher->id, $monthYear);

        return view('teachers.payroll', compact(
            'studio', 'teacher', 'report', 'monthYear', 'subdomain'
        ));
    }

    /**
     * Procesa el pago (Manual o Mercado Pago).
     * El teacher_id se toma del modelo inyectado, NO del formulario.
     */
    public function store(Request $request, $subdomain, Teacher $teacher)
    {
        $studioId = Config::get('tenant.studio_id');

        // Auditoría de seguridad explícita (defensa en profundidad)
        if ($teacher->studio_id !== (int) $studioId) {
            abort(403, 'Este profesor no pertenece a tu estudio.');
        }

        $request->validate([
            'month_year'     => 'required|string|regex:/^\d{4}-\d{2}$/',
            'amount'         => 'required|integer|min:1',
            'payment_method' => 'required|in:manual,mercadopago',
        ]);

        $studio = Studio::findOrFail($studioId);

        // === RUTA MANUAL ===
        if ($request->payment_method === 'manual') {
            $request->validate([
                'receipt' => 'required|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            ]);

            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('payroll_receipts', 'public');
            }

            $payment = TeacherPayment::create([
                'studio_id'      => $studioId,
                'teacher_id'     => $teacher->id,
                'month_year'     => $request->month_year,
                'amount'         => $request->amount,
                'payment_method' => 'manual',
                'receipt_path'   => $receiptPath,
                'status'         => 'paid',
            ]);

            $this->notifyTeacherPaymentReceived($payment, $studio);

            return redirect()->route('teachers.payroll.show', [
                'subdomain' => $subdomain,
                'teacher'   => $teacher->id,
                'month'     => $request->month_year,
            ])->with('success', 'Pago manual registrado correctamente.');
        }

        // === RUTA MERCADO PAGO ===
        if (!$teacher->user || !$teacher->user->mp_access_token) {
            return back()->with('error', 'El profesor no ha vinculado su cuenta de Mercado Pago. Pídele que configure sus cobros desde su portal.');
        }

        try {
            $successUrl = route('payroll.mp.success.global', [
                'teacher_id' => $teacher->id,
                'subdomain'  => $subdomain,
                'month_year' => $request->month_year,
            ]);

            $failureUrl = route('payroll.mp.failure.global', [
                'teacher_id' => $teacher->id,
                'subdomain'  => $subdomain,
            ]);

            $preference = $this->mpService->createTeacherPaymentPreference([
                'teacher_mp_token' => $teacher->user->mp_access_token,
                'title'            => "Liquidación {$request->month_year} - {$studio->name}",
                'amount'           => $request->amount,
                'teacher_id'       => $teacher->id,
                'studio_id'        => $studioId,
                'month_year'       => $request->month_year,
                'success_url'      => $successUrl,
                'failure_url'      => $failureUrl,
            ]);

            // Crear registro pendiente
            TeacherPayment::create([
                'studio_id'      => $studioId,
                'teacher_id'     => $teacher->id,
                'month_year'     => $request->month_year,
                'amount'         => $request->amount,
                'payment_method' => 'mercadopago',
                'status'         => 'pending',
            ]);

            return redirect()->away($preference['init_point']);

        } catch (\Exception $e) {
            Log::error('Error generando pago a profesor vía MP: ' . $e->getMessage(), [
                'teacher_id' => $teacher->id,
                'amount'     => $request->amount,
            ]);
            return back()->with('error', 'Error al generar el pago con Mercado Pago. Verifica que la cuenta del profesor esté activa.');
        }
    }

    /**
     * Callback GLOBAL de éxito desde MercadoPago (dominio principal).
     * Recibe parámetros por query string y redirige al subdominio.
     */
    public function mpSuccessGlobal(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $subdomain = $request->query('subdomain');
        $monthYear = $request->query('month_year');

        if (!$teacherId || !$subdomain) {
            return redirect('/')->with('error', 'Datos de pago incompletos.');
        }

        $teacher = Teacher::withoutGlobalScopes()->find($teacherId);
        if (!$teacher) {
            return redirect('/')->with('error', 'Profesor no encontrado.');
        }

        if ($monthYear) {
            $payment = TeacherPayment::where('teacher_id', $teacher->id)
                ->where('month_year', $monthYear)
                ->where('payment_method', 'mercadopago')
                ->where('status', 'pending')
                ->latest()
                ->first();

            if ($payment) {
                $payment->update(['status' => 'paid']);
                
                $studio = Studio::withoutGlobalScopes()->find($teacher->studio_id);
                if ($studio) {
                    $this->notifyTeacherPaymentReceived($payment, $studio);
                }
            }
        }

        return redirect()->route('teachers.payroll.show', [
            'subdomain' => $subdomain,
            'teacher'   => $teacher->id,
            'month'     => $monthYear,
        ])->with('success', '¡Pago a profesor procesado exitosamente!');
    }

    /**
     * Callback GLOBAL de fallo desde MercadoPago (dominio principal).
     */
    public function mpFailureGlobal(Request $request)
    {
        $teacherId = $request->query('teacher_id');
        $subdomain = $request->query('subdomain');

        if (!$teacherId || !$subdomain) {
            return redirect('/')->with('error', 'Datos de pago incompletos.');
        }

        $teacher = Teacher::withoutGlobalScopes()->find($teacherId);
        if (!$teacher) {
            return redirect('/')->with('error', 'Profesor no encontrado.');
        }

        return redirect()->route('teachers.payroll.show', [
            'subdomain' => $subdomain,
            'teacher'   => $teacher->id,
        ])->with('error', 'El pago no fue completado. Puedes intentarlo nuevamente.');
    }

    /**
     * Cancela un pago pendiente (solo si status = pending).
     */
    public function destroy($subdomain, Teacher $teacher, TeacherPayment $payment)
    {
        $studioId = Config::get('tenant.studio_id');
        if ($teacher->studio_id !== (int) $studioId) {
            abort(403);
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Solo se pueden cancelar pagos pendientes.');
        }

        if ($payment->teacher_id !== $teacher->id) {
            abort(403);
        }

        $payment->delete();

        return redirect()->route('teachers.payroll.show', [
            'subdomain' => $subdomain,
            'teacher'   => $teacher->id,
            'month'     => $payment->month_year,
        ])->with('success', 'Intento de pago cancelado.');
    }

    /**
     * Retoma un pago pendiente con MercadoPago generando una nueva Preference.
     */
    public function resume($subdomain, Teacher $teacher, TeacherPayment $payment)
    {
        $studioId = Config::get('tenant.studio_id');
        if ($teacher->studio_id !== (int) $studioId) {
            abort(403);
        }

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Solo se pueden retomar pagos pendientes.');
        }

        if ($payment->payment_method !== 'mercadopago') {
            return back()->with('error', 'Solo se pueden retomar pagos de MercadoPago.');
        }

        if ($payment->teacher_id !== $teacher->id) {
            abort(403);
        }

        if (!$teacher->user || !$teacher->user->mp_access_token) {
            return back()->with('error', 'El profesor no tiene cuenta de MercadoPago vinculada.');
        }

        $studio = Studio::findOrFail($studioId);

        try {
            $successUrl = route('payroll.mp.success.global', [
                'teacher_id' => $teacher->id,
                'subdomain'  => $subdomain,
                'month_year' => $payment->month_year,
            ]);

            $failureUrl = route('payroll.mp.failure.global', [
                'teacher_id' => $teacher->id,
                'subdomain'  => $subdomain,
            ]);

            $preference = $this->mpService->createTeacherPaymentPreference([
                'teacher_mp_token' => $teacher->user->mp_access_token,
                'title'            => "Liquidación {$payment->month_year} - {$studio->name}",
                'amount'           => $payment->amount,
                'teacher_id'       => $teacher->id,
                'studio_id'        => $studioId,
                'month_year'       => $payment->month_year,
                'success_url'      => $successUrl,
                'failure_url'      => $failureUrl,
            ]);

            return redirect()->away($preference['init_point']);

        } catch (\Exception $e) {
            Log::error('Error retomando pago a profesor: ' . $e->getMessage());
            return back()->with('error', 'Error al generar el pago. Verifica que la cuenta del profesor esté activa.');
        }
    }

    /**
     * Dispara notificación in-app y correo cuando un pago se marca como pagado.
     */
    private function notifyTeacherPaymentReceived(TeacherPayment $payment, Studio $studio): void
    {
        $user = $payment->teacher?->user;
        if (! $user) {
            return;
        }

        // Notificación in-app (campanita)
        try {
            $user->notify(new \App\Notifications\TeacherPaymentReceivedNotification($payment));
        } catch (\Exception $e) {
            Log::error('Error enviando notificación in-app de pago a profesor: ' . $e->getMessage());
        }

        // Correo electrónico (encolado, no bloquea la respuesta)
        try {
            Mail::to($user->email)->queue(new \App\Mail\TeacherPaymentReceivedMail($payment, $studio));
        } catch (\Exception $e) {
            Log::error('Error encolando correo de pago a profesor: ' . $e->getMessage());
        }
    }
}
