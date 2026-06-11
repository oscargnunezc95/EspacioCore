<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\ClassSession;
use App\Models\TeacherPayment;
use App\Models\Studio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class PayrollService
{
    /**
     * Obtiene las ClassSessions completadas de un profesor en un mes,
     * con sus asistencias y un subtotal sugerido.
     *
     * @return array{teacher: Teacher, sessions: \Illuminate\Support\Collection, subtotal: int, month_year: string, payments: \Illuminate\Support\Collection}
     */
    public function getMonthlyReport(int $teacherId, string $monthYear): array
    {
        $teacher = Teacher::with('user')->findOrFail($teacherId);

        $date = Carbon::createFromFormat('Y-m', $monthYear);

        // Sesiones del mes de este profesor (solo las pasadas, no canceladas)
        $sessions = ClassSession::withoutGlobalScopes()
            ->with([
                'workshop' => fn($q) => $q->withoutGlobalScopes(),
                'workshop.teacher',
                // CORRECCIÓN 1: Evitar que el Global Scope oculte las asistencias
                'attendances' => fn($q) => $q->withoutGlobalScopes(), 
                'schedule',
            ])
            ->whereHas('workshop', function ($query) use ($teacherId) {
                $query->withoutGlobalScopes()->where('teacher_id', $teacherId);
            })
            ->where('is_cancelled', false)
            ->whereYear('date', $date->year)
            ->whereMonth('date', $date->month)
            // CORRECCIÓN 2: Comentamos el filtro de "hoy" para que el profesor 
            // y el dueño puedan ver TODO el mes proyectado (pasado y futuro).
            // Si estrictamente quieres ocultar el futuro, descomenta esta línea:
            // ->where('date', '<=', now()->toDateString()) 
            ->orderBy('date', 'asc')
            ->orderBy('start_time', 'asc')
            ->get();

        // Calcular subtotal sugerido: sumar la cantidad de asistencias * un valor base
        $subtotal = 0;
        foreach ($sessions as $session) {
            // Ahora sí contará correctamente los registros de la base de datos
            $subtotal += $session->attendances->count();
        }

        $payments = TeacherPayment::where('teacher_id', $teacherId)
            ->where('month_year', $monthYear)
            ->latest()
            ->get();

        return [
            'teacher'    => $teacher,
            'sessions'   => $sessions,
            'subtotal'   => $subtotal, 
            'month_year' => $monthYear,
            'payments'   => $payments,
        ];
    }

    /**
     * Calcula el total pagado hasta ahora en el mes.
     */
    public function getTotalPaid(int $teacherId, string $monthYear): int
    {
        return TeacherPayment::where('teacher_id', $teacherId)
            ->where('month_year', $monthYear)
            ->where('status', 'paid') 
            ->sum('amount');
    }

    /**
     * Procesa un pago manual a profesor: almacena el comprobante y crea el registro.
     * Extraído de PayrollController@store para cumplir con "Controladores delgados, Servicios robustos".
     *
     * @return TeacherPayment
     */
    public function processManualPayment(Request $request, Teacher $teacher, int $studioId): TeacherPayment
    {
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

        $this->notifyTeacherPaymentReceived($payment);

        return $payment;
    }

    /**
     * Crea un registro de pago pendiente (Mercado Pago) para un profesor.
     * Extraído de PayrollController@store (ruta MP).
     *
     * @return TeacherPayment
     */
    public function createPendingPayment(array $data): TeacherPayment
    {
        return TeacherPayment::create([
            'studio_id'      => $data['studio_id'],
            'teacher_id'     => $data['teacher_id'],
            'month_year'     => $data['month_year'],
            'amount'         => $data['amount'],
            'payment_method' => 'mercadopago',
            'status'         => 'pending',
        ]);
    }

    /**
     * Busca el pago pendiente más reciente de un profesor en un mes y lo marca como 'paid'.
     * Extraído de PayrollController@mpSuccessGlobal.
     *
     * @return TeacherPayment|null
     */
    public function markPaymentAsPaid(int $teacherId, string $monthYear): ?TeacherPayment
    {
        $payment = TeacherPayment::where('teacher_id', $teacherId)
            ->where('month_year', $monthYear)
            ->where('payment_method', 'mercadopago')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if ($payment) {
            $payment->update(['status' => 'paid']);

            $studio = Studio::withoutGlobalScopes()->find($payment->studio_id);
            if ($studio) {
                $this->notifyTeacherPaymentReceived($payment);
            }
        }

        return $payment;
    }

    /**
     * Dispara notificación in-app y correo cuando un pago se marca como pagado.
     * Extraído de PayrollController para reutilización.
     */
    public function notifyTeacherPaymentReceived(TeacherPayment $payment): void
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
            $studio = Studio::withoutGlobalScopes()->find($payment->studio_id);
            Mail::to($user->email)->queue(new \App\Mail\TeacherPaymentReceivedMail($payment, $studio));
        } catch (\Exception $e) {
            Log::error('Error encolando correo de pago a profesor: ' . $e->getMessage());
        }
    }
}
