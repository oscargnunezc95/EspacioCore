<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\ClassSession;
use App\Models\Attendance;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Filtrado de Ganancias por Mes
        $selectedMonth = $request->get('month', Carbon::now()->format('Y-m'));
        $monthDate = Carbon::parse($selectedMonth . '-01');

        $monthlyIncome = Payment::whereYear('created_at', $monthDate->year)
            ->whereMonth('created_at', $monthDate->month)
            ->sum('amount');

        // 2. Clases de Hoy
        $todaysClasses = ClassSession::with('workshop')
            ->whereDate('date', Carbon::today())
            ->orderBy('start_time', 'asc')
            ->get();

        // 3. NUEVA LÓGICA DE DEUDAS:
        // Buscamos asistencias que NO tengan un ID en la tabla pivot class_session_payment
        $unpaidAttendances = Attendance::with(['student', 'classSession.workshop'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('class_session_payment')
                    ->whereColumn('class_session_payment.class_session_id', 'attendances.class_session_id')
                    ->whereColumn('class_session_payment.student_id', 'attendances.student_id');
            })
            ->get();

        return view('dashboard', compact(
            'monthlyIncome', 
            'todaysClasses', 
            'unpaidAttendances', 
            'selectedMonth'
        ));
    }
}