<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Payment;
use App\Models\ClassSession;
use App\Models\Studio;
use App\Models\Promotion;
use App\Mail\StudentPaymentReceiptMail;
use App\Mail\StudioPaymentNotificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Procesa el pago manual (Efectivo/Transferencia) realizado por la dueña del estudio.
     * Vincula el monto a sesiones específicas, automatiza la asistencia y notifica por correo.
     */
    public function store(Request $request, $subdomain, Student $student)
    {
        // 1. Validación estricta de la entrada
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:efectivo,transferencia', 
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf|max:10240',
            'session_ids' => 'required|array|min:1', 
            'session_ids.*' => 'exists:class_sessions,id'
        ], [
            'session_ids.required' => 'Debes seleccionar al menos una clase del calendario.',
            'amount.required' => 'El monto del pago es obligatorio.',
            'payment_method.required' => 'Debes seleccionar el método de pago.'
        ]);

        // 2. Gestión de archivo de comprobante
        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        // Recuperamos la primera sesión para determinar el taller asociado (contexto contable)
        $firstSession = ClassSession::findOrFail($request->session_ids[0]);

        // Resolvemos el Estudio actual mediante el subdominio para el contexto de los correos
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        // 3. Ejecución de la transacción de Base de Datos (Retorna el pago creado)
        $payment = DB::transaction(function () use ($request, $student, $path, $firstSession) {
            
            $payment = Payment::create([
                'student_id'     => $student->id,
                'workshop_id'    => $firstSession->workshop_id,
                'payment_type'   => count($request->session_ids) == 1 ? 'single' : 'pack',
                'payment_method' => $request->payment_method, 
                'amount'         => $request->amount,
                'receipt_path'   => $path
            ]);

            // 4. Vinculación en tabla pivot (Historial del pago)
            $pivotData = [];
            foreach ($request->session_ids as $sessionId) {
                $pivotData[$sessionId] = ['student_id' => $student->id];
            }
            $payment->classSessions()->attach($pivotData);

            // 5. AUTOMATIZACIÓN DE FLUJO: Inscripción + Asistencia
            foreach ($request->session_ids as $sessionId) {
                $session = ClassSession::find($sessionId);
                
                // Asegurar inscripción Y marcar como PAGADO (Para vaciar el carrito)
                $session->students()->syncWithoutDetaching([
                    $student->id => ['payment_status' => 'paid']
                ]);
                
                // Marcar presente automáticamente
                $session->attendances()->firstOrCreate([
                    'student_id' => $student->id
                ]);
            }

            return $payment;
        });

        // 6. FLUJO DE COMUNICACIÓN: Envíos de correo fuera de la transacción
        try {
            // Notificación al Alumno (si cuenta con correo registrado)
            if ($student->email) {
                Mail::to($student->email)->send(
                    new StudentPaymentReceiptMail($studio, $payment, $student->name)
                );
            }

            // Notificación al Administrador del Estudio
            if ($studio->user && $studio->user->email) {
                Mail::to($studio->user->email)->send(
                    new StudioPaymentNotificationMail($studio, $payment, $student->name)
                );
            }
        } catch (\Throwable $e) { 
            \Illuminate\Support\Facades\Log::error('Error enviando correos de pago: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            
            // Retornamos de vuelta a la vista (Blade) con el error para no romper la UX
            return back()->withErrors('El pago y la asistencia se registraron correctamente, pero hubo un problema al enviar los correos de confirmación.');
        }

        return back()->with('success', '¡Pago y asistencia registrados correctamente!');
    }

    /**
     * Actualiza una promoción existente en la base de datos.
     * @note Se recomienda migrar este método a un PromotionController dedicado.
     */
    public function update(Request $request, $subdomain, Promotion $promotion)
    {
        // 1. Validación estricta de los datos entrantes
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:specific_combo,additional_discount',
            'total_price' => 'nullable|required_if:type,specific_combo|numeric|min:0',
            'workshop_price_ids' => 'nullable|required_if:type,specific_combo|array',
            'workshop_price_ids.*' => 'exists:workshop_prices,id', 
            'class_count' => 'nullable|required_if:type,additional_discount|integer|min:1',
            'additional_price' => 'nullable|required_if:type,additional_discount|numeric|min:0',
        ]);

        // 2. Limpieza de estado preventivo
        if ($validated['type'] === 'specific_combo') {
            $validated['class_count'] = null;
            $validated['additional_price'] = null;
        } else {
            $validated['total_price'] = null;
        }

        // 3. Actualización de la entidad principal
        $promotion->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'total_price' => $validated['total_price'],
            'class_count' => $validated['class_count'],
            'additional_price' => $validated['additional_price'],
        ]);

        // 4. Sincronización inteligente de la tabla pivote
        if ($validated['type'] === 'specific_combo' && !empty($validated['workshop_price_ids'])) {
            $promotion->workshopPrices()->sync($validated['workshop_price_ids']);
        } else {
            $promotion->workshopPrices()->detach();
        }

        return redirect()->route('promotions.index', ['subdomain' => $subdomain])
                         ->with('success', 'Regla de descuento actualizada correctamente.');
    }

    /**
     * Anula un pago realizado.
     */
    public function destroy($subdomain, Payment $payment)
    {
        if ($payment->receipt_path) {
            Storage::disk('public')->delete($payment->receipt_path);
        }

        // REVERSIÓN ARQUITECTÓNICA: Devolver la deuda al carrito
        $sessionIds = $payment->classSessions()->pluck('class_sessions.id');
        
        if ($sessionIds->isNotEmpty()) {
            $pivotData = [];
            foreach ($sessionIds as $id) {
                $pivotData[$id] = ['payment_status' => 'pending'];
            }
            $payment->student->classSessions()->syncWithoutDetaching($pivotData);
        }

        $payment->delete();

        return back()->with('success', 'Pago anulado. Las clases vuelven a figurar como pendientes de pago.');
    }

    /**
     * API Endpoint: Retorna sesiones disponibles para cobro (que no han sido pagadas aún)
     */
    public function getAvailableSessions($subdomain, Student $student)
    {
        // MAGIA DE OPTIMIZACIÓN: Consulta directa a la base de datos usando el estado pivote
        $sessions = ClassSession::with('workshop')
            ->where('date', '>=', now()->startOfMonth())
            ->whereDoesntHave('students', function ($query) use ($student) {
                $query->where('students.id', $student->id)
                      ->wherePivot('payment_status', 'paid');
            })
            ->orderBy('date', 'asc')
            ->get();

        $formatted = $sessions->map(function ($session) {
            return [
                'id'             => $session->id,
                'workshop_name'  => $session->workshop->name,
                'formatted_date' => ucfirst(Carbon::parse($session->date)->translatedFormat('l d \d\e F')),
                'time'           => Carbon::parse($session->start_time)->format('H:i')
            ];
        });

        return response()->json($formatted);
    }
}