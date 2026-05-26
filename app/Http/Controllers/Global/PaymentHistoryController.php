<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Payment;

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Obtenemos los IDs de los perfiles (Titular + Familiares)
        $studentIds = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        if (empty($studentIds)) {
            $payments = collect();
            return view('global.payments.index', compact('payments'));
        }

        // 2. Traemos todos los pagos de la familia entera
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
        ->paginate(15);

        return view('global.payments.index', compact('payments'));
    }
}