<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Payment;
use App\Models\ClassSession;
use App\Models\Studio;
use App\Models\Promotion;
use App\Services\EnrollmentService;
use App\Mail\StudentPaymentReceiptMail;
use App\Mail\StudioPaymentNotificationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Services\PricingService;
use App\Services\MercadoPagoService;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Procesa el pago manual (Efectivo/Transferencia) realizado por la dueña del estudio.
     * Vincula el monto a sesiones específicas, automatiza la asistencia y notifica por correo.
     */
    public function store(Request $request, $subdomain, Student $student)
    {
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

        $path = null;
        if ($request->hasFile('receipt')) {
            $path = $request->file('receipt')->store('receipts', 'public');
        }

        $firstSession = ClassSession::findOrFail($request->session_ids[0]);
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $payment = DB::transaction(function () use ($request, $student, $path, $firstSession) {
            
            $payment = Payment::create([
                'student_id'     => $student->id,
                'workshop_id'    => $firstSession->workshop_id,
                'payment_type'   => count($request->session_ids) == 1 ? 'single' : 'pack',
                'payment_method' => $request->payment_method, 
                'amount'         => $request->amount,
                'receipt_path'   => $path
            ]);

            $pivotData = [];
            foreach ($request->session_ids as $sessionId) {
                $pivotData[$sessionId] = ['student_id' => $student->id];
            }
            $payment->classSessions()->attach($pivotData);

            foreach ($request->session_ids as $sessionId) {
                $session = ClassSession::find($sessionId);
                
                $session->students()->syncWithoutDetaching([
                    $student->id => ['payment_status' => 'paid']
                ]);
                
                $session->attendances()->firstOrCreate([
                    'student_id' => $student->id
                ]);
            }

            return $payment;
        });

        try {
            if ($student->email) {
                Mail::to($student->email)->queue(
                    new StudentPaymentReceiptMail($studio, $payment, $student->name)
                );
            }

            if ($studio->user && $studio->user->email) {
                Mail::to($studio->user->email)->send(
                    new StudioPaymentNotificationMail($studio, $payment, $student->name)
                );
            }
        } catch (\Throwable $e) { 
            \Illuminate\Support\Facades\Log::error('Error enviando correos de pago: ' . $e->getMessage() . ' en la línea ' . $e->getLine());
            return back()->withErrors('El pago y la asistencia se registraron correctamente, pero hubo un problema al enviar los correos de confirmación.');
        }

        // 7. NOTIFICACIONES: Avisar a estudiantes pendientes si la clase se llenó
        $enrollmentService = app(EnrollmentService::class);
        $enrollmentService->notifyCapacityChange(
            $request->session_ids,
            $student->user_id ?: 0,
            'payment'
        );

        return back()->with('success', '¡Pago y asistencia registrados correctamente!');
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

    // =========================================================================
    // GATEWAY DE PAGO: API ENDPOINTS PARA EL CALENDARIO
    // =========================================================================

    /**
     * Calcula el precio de las sesiones seleccionadas usando PricingService.
     * GET /api/students/{student}/gateway/calculate?session_ids[]=1&session_ids[]=2
     */
    public function calculateGatewayPrice(Request $request, $subdomain, Student $student)
    {
        $request->validate([
            'session_ids'   => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:class_sessions,id',
        ]);

        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $pricingService = app(PricingService::class);

        $result = $pricingService->calculateCart(
            $studio->id,
            $request->session_ids,
            $student->id
        );

        return response()->json([
            'total'          => $result['total'],
            'currency_symbol' => $studio->currency_symbol,
            'breakdown'      => $result['breakdown'],
        ]);
    }

    /**
     * Genera una orden de pago con QR estático de MercadoPago.
     * POST /api/students/{student}/gateway/static-qr
     */
    public function generateStaticQROrder(Request $request, $subdomain, Student $student)
    {
        $request->validate([
            'session_ids'   => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:class_sessions,id',
        ]);

        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $mpService = app(MercadoPagoService::class);

        try {
            $data = $mpService->generateStaticQROrder($studio, $request->session_ids, $student);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Genera una preferencia de Checkout Pro para QR dinámico.
     * POST /api/students/{student}/gateway/dynamic-qr
     */
    public function generateDynamicQR(Request $request, $subdomain, Student $student)
    {
        $request->validate([
            'session_ids'   => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:class_sessions,id',
        ]);

        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $mpService = app(MercadoPagoService::class);

        try {
            $data = $mpService->generateGatewayPreference($studio, $request->session_ids, $student);
            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Genera link de pago y lo envía por correo al estudiante.
     * POST /api/students/{student}/gateway/send-email
     */
    public function sendPaymentEmail(Request $request, $subdomain, Student $student)
    {
        $request->validate([
            'session_ids'   => 'required|array|min:1',
            'session_ids.*' => 'integer|exists:class_sessions,id',
        ]);

        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $mpService = app(MercadoPagoService::class);

        try {
            $data = $mpService->sendPaymentEmail($studio, $request->session_ids, $student);
            return response()->json([
                'message' => 'Link de pago enviado exitosamente.',
                'total'   => $data['total'],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}