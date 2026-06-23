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

        // 1. Mis propias fichas de alumno (yo asisto)
        $ownStudentIds = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        // 2. Fichas de mis familiares/dependientes (ellos asisten, yo gestiono)
        $dependentNationalIds = \App\Models\UserDependent::where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('national_id')
            ->toArray();

        $dependentStudentIds = [];
        if (!empty($dependentNationalIds)) {
            $dependentStudentIds = Student::withoutGlobalScopes()
                ->whereIn('national_id', $dependentNationalIds)
                ->where(function ($q) use ($user) {
                    // Excluir mis propias fichas (por si acaso estoy como dependiente en otro lado)
                    $q->whereNull('user_id')
                      ->orWhere('user_id', '!=', $user->id);
                })
                ->pluck('id')
                ->toArray();
        }

        // 3. Unimos todos los IDs para el Ledger de pagos
        $allStudentIds = array_unique(array_merge($ownStudentIds, $dependentStudentIds));

        if (empty($allStudentIds)) {
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
                ->whereIn('student_id', $allStudentIds)
                ->orderBy('created_at', 'desc')
                ->paginate(15, ['*'], 'payments_page');
        }

        // 4. Ingresos recibidos como profesor (TeacherPayment)
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

        // 5. Estado de vinculación MP del usuario (para mostrar botón en pestaña ingresos)
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
