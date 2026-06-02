<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Payment;
use App\Models\TeacherPayment;
use Illuminate\Support\Facades\Log;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Pagos realizados como alumno (original)
        $studentIds = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            $payments = collect();
        } else {
            $payments = Payment::withoutGlobalScopes()
                ->with([
                    'student' => fn($q) => $q->withoutGlobalScopes(),
                    'student.studio' => fn($q) => $q->withoutGlobalScopes(),
                    'classSessions' => fn($q) => $q->withoutGlobalScopes(),
                    'classSessions.workshop' => fn($q) => $q->withoutGlobalScopes(),
                    'classSessions.workshop.studio' => fn($q) => $q->withoutGlobalScopes(),
                ])
                ->whereIn('student_id', $studentIds)
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'payments_page');
        }

        // 2. Ingresos recibidos como profesor (TeacherPayment)
        $teacherPayments = TeacherPayment::withoutGlobalScopes()
            ->with([
                'teacher' => fn($q) => $q->withoutGlobalScopes(),
                'studio'  => fn($q) => $q->withoutGlobalScopes(),
            ])
            ->whereHas('teacher', function ($q) use ($user) {
                $q->withoutGlobalScopes()->where('user_id', $user->id);
            })
            ->where('status', 'paid')
            ->orderBy('created_at', 'desc')
            ->paginate(15, ['*'], 'income_page');

        // 3. Estado de vinculación MP del usuario (para mostrar botón en pestaña ingresos)
        $mpLinked = !empty($user->mp_access_token);

        return view('global.payments.index', compact('payments', 'teacherPayments', 'mpLinked'));
    }

    public function disconnectMercadoPago()
    {
        $user = Auth::user();
        $user->update([
            'mp_access_token'  => null,
            'mp_refresh_token' => null,
            'mp_user_id'       => null,
        ]);

        Log::info("Usuario {$user->id} desvinculó su cuenta de Mercado Pago.");

        return redirect()->route('global.payments.index')
                         ->with('success', 'Cuenta de Mercado Pago desvinculada.');
    }
}
