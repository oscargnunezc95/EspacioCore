<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Payment;
use Carbon\Carbon;

class ClassSessionController extends Controller
{
    public function show(ClassSession $session)
    {
        $session->load(['workshop', 'attendances']);
        
        // Alumnas Activas para la lista principal
        $students = Student::where('is_guest', false)->orderBy('name', 'asc')->get();
        
        // Alumnas Inactivas (Deshabilitadas) para el nuevo modal
        $inactiveStudents = Student::onlyTrashed()->orderBy('name', 'asc')->get();
        
        $monthId = Carbon::parse($session->date)->format('Y-m');
        
        return view('sessions.show', compact('session', 'students', 'inactiveStudents', 'monthId'));
    }

    public function cancel(ClassSession $session)
    {
        $session->update(['is_cancelled' => !$session->is_cancelled]);
        $status = $session->is_cancelled ? 'cancelada' : 'restaurada';
        return back()->with('success', "Clase $status correctamente.");
    }

    // NUEVO MÉTODO: Registrar alumna no frecuente (Deshabilitada)
    public function storeInfrequent(Request $request, ClassSession $session)
    {
        $request->validate([
            'infrequent_mode' => 'required|in:existing,new',
            'student_id' => 'required_if:infrequent_mode,existing|nullable|exists:students,id',
            'rut' => 'required_if:infrequent_mode,new|nullable|string|unique:students,rut',
            'name' => 'required_if:infrequent_mode,new|nullable|string|max:255',
            // Ahora amount y receipt son opcionales (nullable)
            'amount' => 'nullable|numeric|min:0',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240'
        ], [
            'rut.unique' => 'Este RUT ya está registrado. Búscala en la opción "Buscar Existente".',
            'student_id.required_if' => 'Debes seleccionar una alumna de la lista.',
            'rut.required_if' => 'Debes ingresar el RUT.',
            'name.required_if' => 'Debes ingresar el nombre.'
        ]);

        if ($request->infrequent_mode === 'existing') {
            $student = Student::withTrashed()->findOrFail($request->student_id);
        } else {
            $student = Student::create([
                'rut' => $request->rut,
                'name' => $request->name,
            ]);
            $student->delete();
        }

        // 1. Manejo condicional del comprobante
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        // 2. Solo registramos el pago si se ingresó un monto
        // Si no hay monto, la alumna simplemente queda con la asistencia marcada
        if ($request->filled('amount')) {
            $payment = Payment::create([
                'student_id' => $student->id,
                'workshop_id' => $session->workshop_id,
                'payment_type' => 'single',
                'amount' => $request->amount,
                'receipt_path' => $path
            ]);

            // Vincular el pago a esta clase específica
            $payment->classSessions()->attach($session->id, ['student_id' => $student->id]);
        }
        
        // 3. La asistencia se marca SIEMPRE, haya pagado o no
        $session->attendances()->firstOrCreate(['student_id' => $student->id]);

        return back()->with('success', 'Alumna no frecuente procesada correctamente.');
    }
}