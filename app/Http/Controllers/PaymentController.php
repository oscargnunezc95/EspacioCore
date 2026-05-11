<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Payment;
use App\Models\ClassSession;
// ELIMINADO: use App\Models\Attendance;
// ELIMINADO: use App\Models\Studio;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Procesa el pago manual (Efectivo/Transferencia) realizado por la dueña del estudio.
     * Vincula el monto a sesiones específicas y automatiza la asistencia.
     */
    public function store(Request $request, $subdomain, Student $student)
    {
        // 1. Validación estricta de la entrada
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'session_ids' => 'required|array|min:1', 
            'session_ids.*' => 'exists:class_sessions,id'
        ], [
            'session_ids.required' => 'Debes seleccionar al menos una clase del calendario.',
            'amount.required' => 'El monto del pago es obligatorio.'
        ]);

        // 2. Gestión de archivo de comprobante
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        // Recuperamos la primera sesión para determinar el taller asociado (contexto contable)
        $firstSession = ClassSession::findOrFail($request->session_ids[0]);

        return DB::transaction(function () use ($request, $student, $path, $firstSession, $subdomain) {
            
            // 3. Crear el registro maestro del Pago
            $payment = Payment::create([
                'student_id'   => $student->id,
                'workshop_id'  => $firstSession->workshop_id,
                'payment_type' => count($request->session_ids) == 1 ? 'single' : 'pack',
                'amount'       => $request->amount,
                'receipt_path' => $path
            ]);

            // 4. Vinculación en tabla pivot usando la relación limpia (Conciliación)
            $pivotData = [];
            foreach ($request->session_ids as $sessionId) {
                $pivotData[$sessionId] = ['student_id' => $student->id];
            }
            $payment->classSessions()->attach($pivotData);

            // 5. AUTOMATIZACIÓN DE FLUJO: Inscripción + Asistencia
            foreach ($request->session_ids as $sessionId) {
                $session = ClassSession::find($sessionId);
                
                // 5.1 Asegurar inscripción (reserva de cupo)
                $session->students()->syncWithoutDetaching([$student->id]);
                
                // 5.2 Marcar presente automáticamente a través de la RELACIÓN
                // Esto es mucho más seguro que llamar a Attendance::
                $session->attendances()->firstOrCreate([
                    'student_id' => $student->id
                ]);
            }

            return back()->with('success', '¡Pago y asistencia registrados correctamente!');
        });
    }

    /**
     * Anula un pago realizado.
     * Gracias a cascadeOnDelete, se limpia la relación de clases pagadas,
     * pero mantenemos la asistencia por integridad histórica.
     */
    public function destroy($subdomain, Payment $payment)
    {
        if ($payment->receipt_path) {
            Storage::disk('public')->delete($payment->receipt_path);
        }

        $payment->delete();

        return back()->with('success', 'Pago anulado. Las clases vuelven a figurar como pendientes de pago.');
    }

    /**
     * API Endpoint: Retorna sesiones disponibles para cobro (que no han sido pagadas aún)
     */
    public function getAvailableSessions($subdomain, Student $student)
    {
        // 1. IDs de sesiones ya pagadas por este alumno, consultadas de forma 100% Eloquent
        $paidSessionIds = Payment::where('student_id', $student->id)
            ->with('classSessions')
            ->get()
            ->flatMap(function($payment) {
                return $payment->classSessions->pluck('id');
            })
            ->unique()
            ->toArray();

        // 2. Buscamos sesiones futuras que NO estén en la lista de pagadas.
        // NOTA: El middleware del tenant ya inyecta automáticamente el 'studio_id', no necesitamos re-filtrarlo.
        $sessions = ClassSession::with('workshop')
            ->where('date', '>=', now()->startOfMonth())
            ->whereNotIn('id', $paidSessionIds)
            ->orderBy('date', 'asc')
            ->get();

        $formatted = $sessions->map(function ($session) {
            return [
                'id'            => $session->id,
                'workshop_name' => $session->workshop->name,
                'formatted_date'=> ucfirst(Carbon::parse($session->date)->translatedFormat('l d \d\e F')),
                'time'          => Carbon::parse($session->start_time)->format('H:i')
            ];
        });

        return response()->json($formatted);
    }
}