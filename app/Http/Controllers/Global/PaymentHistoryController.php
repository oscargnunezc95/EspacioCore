<?php

namespace App\Http\Controllers\Global;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Student;
// ELIMINADO: use App\Models\Session; (Peligro de colisión)
use App\Models\ClassSession; // Importación segura y semántica
use App\Models\Payment;

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

        // 3. Obtenemos los pagos con paginación y Eager Loading para rendimiento
        $payments = Payment::withoutGlobalScopes()
        ->with([
            'student' => fn($q) => $q->withoutGlobalScopes(), 
            'student.studio',
            
            // LA MAGIA: Actualizado a classSessions para que coincida con el Modelo limpio
            'classSessions' => fn($q) => $q->withoutGlobalScopes(),
            'classSessions.workshop' => fn($q) => $q->withoutGlobalScopes(),
        ])
        ->whereIn('student_id', $studentIds)
        ->orderBy('created_at', 'desc')
        ->paginate(15);

        return view('global.payments.index', compact('payments'));
    }
}