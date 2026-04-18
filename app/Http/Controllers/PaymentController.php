<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Workshop;
use App\Models\Payment;
use App\Models\ClassSession;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PaymentController extends Controller
{
    // Muestra la vista para registrar el pago
    public function create(Student $student)
    {
        $workshops = Workshop::orderBy('name', 'asc')->get();
        return view('payments.create', compact('student', 'workshops'));
    }

    // Procesa el pago y lo amarra a las fechas específicas
    public function store(Request $request, Student $student)
    {
        $request->validate([
            'workshop_id' => 'required|exists:workshops,id',
            'amount' => 'required|numeric|min:0',
            'receipt' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
            'sessions' => 'required|array|min:1', // Exige que seleccione al menos 1 clase
            'sessions.*' => 'exists:class_sessions,id'
        ], [
            'sessions.required' => 'Debes seleccionar al menos una clase del calendario para aplicar el pago.',
            'amount.required' => 'Debes ingresar el monto del pago.'
        ]);

        // 1. Manejar el archivo del comprobante
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        // 2. Guardar el registro del pago principal
        $payment = Payment::create([
            'student_id' => $student->id,
            'workshop_id' => $request->workshop_id,
            'payment_type' => count($request->sessions) == 1 ? 'single' : 'pack',
            'amount' => $request->amount,
            'receipt_path' => $path
        ]);

        // 3. LA MAGIA: Amarrar el pago a las sesiones seleccionadas
        $pivotData = [];
        foreach ($request->sessions as $sessionId) {
            // Guardamos también el student_id en la tabla pivot para saber de quién es el cupo
            $pivotData[$sessionId] = ['student_id' => $student->id];
        }
        $payment->classSessions()->attach($pivotData);

        // 4. (Opcional) Asegurarnos de que la alumna esté vinculada al taller en general
        $student->workshops()->syncWithoutDetaching([$request->workshop_id]);

        return redirect()->route('students.index')->with('success', '¡Pago registrado! Las clases seleccionadas ya están cubiertas.');
    }

    // -------------------------------------------------------------
    // NUEVO MÉTODO: Busca las clases impagas para el JavaScript
    // -------------------------------------------------------------
    public function getAvailableSessions(Student $student)
    {
        // 1. Buscar los IDs de las sesiones que esta alumna YA PAGÓ
        $paidSessionIds = DB::table('class_session_payment')
            ->where('student_id', $student->id)
            ->pluck('class_session_id')
            ->toArray();

        // 2. Buscamos TODAS las sesiones desde inicio de mes (sin importar el taller)
        $sessions = ClassSession::with('workshop')
            ->where('date', '>=', now()->startOfMonth())
            ->whereNotIn('id', $paidSessionIds)
            ->orderBy('date', 'asc')
            ->get();

        // 3. Formateamos enviando el nombre del taller incluido
        $formattedSessions = $sessions->map(function ($session) {
            $date = Carbon::parse($session->date);
            return [
                'id' => $session->id,
                'workshop_name' => $session->workshop->name, // <- AHORA MANDAMOS EL NOMBRE
                'formatted_date' => ucfirst($date->translatedFormat('l d \d\e F')),
                'time' => Carbon::parse($session->start_time)->format('H:i')
            ];
        });

        return response()->json($formattedSessions);
    }
    public function destroy(Payment $payment)
    {
        // 1. Borrar la foto del comprobante del servidor (opcional pero recomendado)
        if ($payment->receipt_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($payment->receipt_path);
        }

        // 2. Borrar el pago. Gracias al "cascadeOnDelete" de la base de datos, 
        // las clases asociadas volverán a estar pendientes automáticamente.
        $payment->delete();

        return back()->with('success', 'Pago anulado correctamente. Las clases volvieron a quedar pendientes.');
    }
}