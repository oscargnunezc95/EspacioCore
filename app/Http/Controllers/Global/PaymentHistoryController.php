<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
use App\Models\Payment;
// ELIMINADO: use App\Models\ClassSession; (Ya no lo necesitamos aquí)

class PaymentHistoryController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Obtenemos los IDs de los perfiles de estudiante en todos los estudios
        $studentIds = Student::withoutGlobalScopes()
            ->where('user_id', $user->id)
            ->pluck('id')
            ->toArray();

        // 2. Si no tiene perfiles, devolvemos la colección vacía
        if (empty($studentIds)) {
            $payments = collect();
            return view('global.payments.index', compact('payments'));
        }

        // 3. Obtenemos los pagos con paginación y Eager Loading SUPER optimizado
        $payments = Payment::withoutGlobalScopes()
        ->with([
            'student' => fn($q) => $q->withoutGlobalScopes(), 
            // Blindamos el estudio de la alumna por si acaso
            'student.studio' => fn($q) => $q->withoutGlobalScopes(), 
            
            // Relaciones limpias
            'classSessions' => fn($q) => $q->withoutGlobalScopes(),
            'classSessions.workshop' => fn($q) => $q->withoutGlobalScopes(),
            // CRÍTICO: Cargamos el estudio del taller para evitar N+1 al renderizar el logo
            'classSessions.workshop.studio' => fn($q) => $q->withoutGlobalScopes(), 
        ])
        ->whereIn('student_id', $studentIds)
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('global.payments.index', compact('payments'));
    }
}