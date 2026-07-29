<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MercadoPagoService;
use App\Services\EnrollmentService;
use App\Services\PricingService;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Payment;
use App\Models\Studio;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    /**
     * Recibe la petición del Carrito y devuelve el init_point de MP,
     * o procesa directamente si el total es $0 (checkout gratuito).
     */
    public function generarCheckout(Request $request, MercadoPagoService $mpService, PricingService $pricingService)
    {
        $request->validate([
            'studio_id' => ['required', 'integer', 'exists:studios,id'],
            'selections' => ['required', 'array'],
            'selections.*.session_id' => ['required', 'integer'],
            'selections.*.student_id' => ['required', 'integer']
        ]);

        try {
            $user = Auth::user();
            $studio = Studio::findOrFail($request->studio_id);

            // ─── CAPA 3 ANTI-OVERBOOKING: Pre-flight check antes de MP ──────
            $selectionsBySession = collect($request->selections)->groupBy('session_id');
            $sessionIds = $selectionsBySession->keys()->toArray();

            if (!empty($sessionIds)) {
                $enrollmentService = app(EnrollmentService::class);
                $capacityInfo = $enrollmentService->getCapacityInfo($sessionIds);

                foreach ($selectionsBySession as $sessionId => $items) {
                    $requested = $items->count();
                    $available = $capacityInfo[$sessionId]['available_spots'] ?? 0;

                    if ($requested > $available) {
                        $session = ClassSession::withoutGlobalScopes()
                            ->with(['workshop' => fn($q) => $q->withoutGlobalScopes()])
                            ->find($sessionId);
                        $sessionName = $session ? $session->workshop->name : 'Clase #' . $sessionId;

                        Log::warning("Layer 3 bloqueo: session {$sessionId} ({$sessionName}) - solicitado {$requested}, disponible {$available}");

                        return response()->json([
                            'error'   => true,
                            'message' => "Lo sentimos, la clase \"{$sessionName}\" ya no tiene cupos suficientes. Solo quedan {$available}.",
                            'code'    => 'CLASS_FULL'
                        ], 422);
                    }
                }
            }

            // ═══════════════════════════════════════════════════════════════
            // 🆕 CHECKOUT GRATUITO: Si el total es $0, procesamos directo
            //    sin pasar por MercadoPago (que no acepta pagos de $0).
            // ═══════════════════════════════════════════════════════════════
            $selectionsByStudent = collect($request->selections)->groupBy('student_id');
            $grandTotal = 0;

            foreach ($selectionsByStudent as $studentId => $selectionItems) {
                $student = Student::withoutGlobalScopes()->find($studentId);
                if (!$student) continue;

                $checkedSessionIds = $selectionItems->pluck('session_id')->toArray();
                $result = $pricingService->calculateCart($request->studio_id, $checkedSessionIds, $student->id);
                $grandTotal += $result['total'];
            }

            if ($grandTotal <= 0) {
                return $this->processFreeCheckout(
                    $request->studio_id,
                    $request->selections,
                    $studio,
                    $user
                );
            }

            // ─── CHECKOUT NORMAL: Total > 0, se envía a MercadoPago ──────
            $preference = $mpService->createPreference(
                $request->studio_id,
                $request->selections,
                $user
            );

            return response()->json(['init_point' => $preference['init_point']]);

        } catch (\Throwable $e) {
            Log::error('Error generando checkout: ' . $e->getMessage());
            return response()->json(['error' => 'Error al generar el pago.'], 500);
        }
    }

    /**
     * Procesa un checkout cuyo total es $0 como pago confirmado directamente,
     * sin pasar por la pasarela de MercadoPago.
     *
     * Flujo:
     * 1. Crea registros de Payment (payment_method = 'gratis', status = 'approved')
     * 2. Actualiza la tabla pivote class_session_student a 'paid'
     * 3. Crea registros de asistencia
     * 4. Envía correos de confirmación al alumno y al estudio
     * 5. Notifica cambios de capacidad a otros usuarios pendientes
     */
    private function processFreeCheckout(int $studioId, array $selections, Studio $studio, $user)
    {
        $selectionsByStudent = collect($selections)->groupBy('student_id');
        $createdPayments = [];

        DB::beginTransaction();
        try {
            foreach ($selectionsByStudent as $studentId => $sels) {
                $firstSel = $sels->first();
                $sessionIds = $sels->pluck('session_id')->toArray();

                $firstSession = ClassSession::with('workshop.studio')
                    ->withoutGlobalScopes()
                    ->find($firstSel['session_id']);

                if (!$firstSession) continue;

                $student = Student::with('user')->withoutGlobalScopes()->find($studentId);
                if (!$student) continue;

                $payment = Payment::create([
                    'student_id'     => $studentId,
                    'studio_id'      => $firstSession->studio_id ?? $studioId,
                    'workshop_id'    => $firstSession->workshop_id,
                    'payment_type'   => count($sessionIds) == 1 ? 'single' : 'pack',
                    'payment_method' => 'gratis',
                    'amount'         => 0,
                    'status'         => 'approved',
                ]);

                $createdPayments[] = [
                    'payment'      => $payment,
                    'student'      => $student,
                    'firstSession' => $firstSession,
                    'sessionIds'   => $sessionIds,
                ];

                // Attach a la tabla pivote class_session_payment
                $pivotData = [];
                foreach ($sessionIds as $sid) {
                    $pivotData[$sid] = ['student_id' => $studentId];
                }
                $payment->classSessions()->attach($pivotData);

                // Marcar como paid en class_session_student y crear asistencia
                foreach ($sessionIds as $sid) {
                    $session = ClassSession::withoutGlobalScopes()->find($sid);
                    if (!$session) continue;

                    // Verificar si ya estaba pagado (idempotencia)
                    $pivotActual = DB::table('class_session_student')
                        ->where('class_session_id', $sid)
                        ->where('student_id', $studentId)
                        ->first();

                    $yaEstabaPagado = $pivotActual && $pivotActual->payment_status === 'paid';

                    if (!$yaEstabaPagado) {
                        $maxStudents = $session->max_students;

                        $updated = DB::update("
                            UPDATE class_session_student
                            SET payment_status = 'paid', updated_at = ?
                            WHERE class_session_id = ?
                              AND student_id = ?
                              AND payment_status = 'pending'
                              AND (
                                  SELECT COUNT(*) FROM (
                                      SELECT 1 FROM class_session_student
                                      WHERE class_session_id = ?
                                        AND payment_status = 'paid'
                                  ) AS _cnt
                              ) < ?
                        ", [now(), $sid, $studentId, $sid, $maxStudents]);

                        if ($updated === 0) {
                            // El UPDATE falló. Puede ser por: (a) ya estaba paid, o
                            // (b) el alumno nunca fue inscrito (no hay fila 'pending').
                            $isEnrolled = DB::table('class_session_student')
                                ->where('class_session_id', $sid)
                                ->where('student_id', $studentId)
                                ->exists();

                            if (!$isEnrolled) {
                                // Auto-inscribir si hay cupo
                                $paidCount = DB::table('class_session_student')
                                    ->where('class_session_id', $sid)
                                    ->where('payment_status', 'paid')
                                    ->count();

                                if ($paidCount < $maxStudents) {
                                    DB::table('class_session_student')->insert([
                                        'class_session_id' => $sid,
                                        'student_id'       => $studentId,
                                        'payment_status'   => 'paid',
                                        'created_at'       => now(),
                                        'updated_at'       => now(),
                                    ]);
                                }
                            }
                        }

                        // Crear asistencia
                        $session->attendances()->firstOrCreate([
                            'student_id' => $studentId,
                            'studio_id'  => $session->studio_id ?? $studioId,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            Log::error('Error en processFreeCheckout: ' . $e->getMessage() . ' - Línea: ' . $e->getLine());
            return response()->json(['error' => 'Error al procesar la reserva gratuita.'], 500);
        }

        // ═══════════════════════════════════════════════════════════════
        // ACCIONES POST-PERSISTENCIA (correos, notificaciones)
        // ═══════════════════════════════════════════════════════════════
        try {
            $affectedSessionIds = collect($selections)->pluck('session_id')->unique()->toArray();
            app(EnrollmentService::class)->notifyCapacityChange($affectedSessionIds, 0, 'payment');
        } catch (\Exception $e) {
            Log::error("Error secundario al notificar cambio de capacidad en checkout gratis: " . $e->getMessage());
        }

        foreach ($createdPayments as $item) {
            try {
                if ($item['student']->user && $item['payment']->status === 'approved') {
                    $studentName = $item['student']->name;
                    $studioForMail = $studio;

                    // 1️⃣ Notificación In-App al alumno
                    $item['student']->user->notify(
                        new \App\Notifications\StudentPaymentApprovedNotification($item['payment'])
                    );

                    // 2️⃣ Correo de comprobante al alumno
                    Mail::to($item['student']->user->email)
                        ->queue(new \App\Mail\StudentPaymentReceiptMail(
                            $studioForMail,
                            $item['payment'],
                            $studentName
                        ));

                    // 3️⃣ Correo de nueva venta al estudio
                    if ($studioForMail->user && $studioForMail->user->email) {
                        Mail::to($studioForMail->user->email)
                            ->queue(new \App\Mail\StudioPaymentNotificationMail(
                                $studioForMail,
                                $item['payment'],
                                $studentName
                            ));
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error enviando correos/notificaciones en checkout gratis: " . $e->getMessage());
            }
        }

        Log::info('Checkout gratuito procesado exitosamente', [
            'studio_id'  => $studioId,
            'user_id'    => $user->id,
            'num_students' => count($createdPayments),
        ]);

        return response()->json([
            'free_checkout' => true,
            'redirect_url'  => '/pagos/exito',
        ]);
    }
}
