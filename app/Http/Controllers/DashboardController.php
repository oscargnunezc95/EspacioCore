<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Workshop;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // <-- CRÍTICO PARA LA LÓGICA DE DEUDAS

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));
        $parsedDate = Carbon::createFromFormat('Y-m', $period);
        $month = $parsedDate->month;
        $year = $parsedDate->year;

        $studentsCount = Student::count();
        $workshopsCount = Workshop::count();

        // Ingresos (Pendiente de conectar cuando hagas el módulo de ventas)
        $monthlyRevenue = 0; 

        $studentsWithDebt = Student::whereHas('attendances', function ($attendanceQuery) {
            $attendanceQuery->whereHas('classSession', function ($sessionQuery) {
                // La clase ya ocurrió Y NO está cancelada
                $sessionQuery->where('date', '<=', now()->toDateString())
                             ->where('is_cancelled', false);
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('class_session_payment')
                      ->whereColumn('class_session_payment.class_session_id', 'attendances.class_session_id')
                      ->whereColumn('class_session_payment.student_id', 'attendances.student_id');
            });
            
        })->with(['attendances' => function ($attendanceQuery) {
            $attendanceQuery->whereHas('classSession', function ($sessionQuery) {
                // Repetimos la protección aquí para que no cargue el item visualmente
                $sessionQuery->where('date', '<=', now()->toDateString())
                             ->where('is_cancelled', false);
            })
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('class_session_payment')
                      ->whereColumn('class_session_payment.class_session_id', 'attendances.class_session_id')
                      ->whereColumn('class_session_payment.student_id', 'attendances.student_id');
            })
            ->with(['classSession' => function($q) {
                $q->orderBy('date', 'desc')->with('workshop');
            }]);
            
        }])->get();

        return view('studios.dashboard', compact(
            'studentsCount', 
            'workshopsCount', 
            'monthlyRevenue', 
            'period', 
            'studentsWithDebt'
        ));
    }
}