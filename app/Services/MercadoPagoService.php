<?php

namespace App\Services;

use App\Models\Studio;
use App\Models\ClassSession;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\PricingService;

class MercadoPagoService
{
    protected $pricingService;

    public function __construct(PricingService $pricingService)
    {
        $this->pricingService = $pricingService;
    }

    public function createPreference($studioId, $selections, $user)
    {
        $studio = Studio::findOrFail($studioId);
        \MercadoPago\SDK::setAccessToken($studio->mp_access_token ?? config('services.mercadopago.token'));

        $items = [];
        // Agrupamos las selecciones recibidas del frontend por student_id
        $selectionsByStudent = collect($selections)->groupBy('student_id');

        foreach ($selectionsByStudent as $studentId => $selectionItems) {
            $student = Student::withoutGlobalScopes()->find($studentId);
            if (!$student) continue;

            $checkedSessionIds = $selectionItems->pluck('session_id')->toArray();

            $result = $this->pricingService->calculateCart($studioId, $checkedSessionIds);

            if ($result['total'] > 0) {
                $item = new \MercadoPago\Item();
                $item->title = 'Reserva Clases - ' . $student->first_name;
                $item->quantity = 1; 
                $item->unit_price = (float) $result['total'];
                $item->currency_id = 'CLP';
                $items[] = $item;
            }
        }

        // 4. Creamos la Preferencia
        $preference = new \MercadoPago\Preference();
        $preference->items = $items;
        
        $preference->external_reference = json_encode([
            'user_id' => $user->id,
            'selections' => $selections,
            'studio_id' => $studioId
        ]);

        $domain = config('app.url');
        $preference->back_urls = [
            'success' => "{$domain}/pagos/success",
            'failure' => "{$domain}/pagos/failure",
            'pending' => "{$domain}/pagos/pending"
        ];
        
        $preference->auto_return = 'approved';
        $preference->save();

        if (!$preference->init_point) {
            throw new \Exception("Error al generar el link de pago con Mercado Pago.");
        }

        return [
            'init_point' => $preference->init_point,
            'preference_id' => $preference->id
        ];
    }

    /**
     * =====================================================================
     * 2. PROCESAMIENTO DEL WEBHOOK (MP -> SISTEMA)
     * =====================================================================
     */
    public function processStudentPayment($dataId, $mpUserId = null)
    {
        // 1. Configuramos el Token (En webhooks usualmente validamos con el Access Token Global)
        \MercadoPago\SDK::setAccessToken(config('services.mercadopago.token'));
        
        $payment = \MercadoPago\Payment::find_by_id($dataId);

        if (!$payment || $payment->status !== 'approved') {
            Log::info("Pago {$dataId} ignorado o no aprobado. Estado: " . ($payment->status ?? 'null'));
            return;
        }

        // 2. Extraemos la metadata que inyectamos en createPreference
        $meta = json_decode($payment->external_reference, true);
        $selectionsPagadas = $meta['selections'] ?? [];

        DB::beginTransaction();
        try {
            foreach ($selectionsPagadas as $sel) {
                $session = ClassSession::withoutGlobalScopes()->find($sel['session_id']);
                
                if ($session) {
                    // Marcamos SOLO al alumno específico que se pagó
                    $session->students()
                        ->withoutGlobalScopes()
                        ->updateExistingPivot($sel['student_id'], ['payment_status' => 'paid']);
                        
                    $session->attendances()->firstOrCreate(['student_id' => $sel['student_id']]);
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error asignando pago {$dataId} en BD: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * =====================================================================
     * 3. SUSCRIPCIONES SAAS (ESTUDIOS -> PLATAFORMA)
     * =====================================================================
     */
    public function getSubscriptionDetails($dataId)
    {
        \MercadoPago\SDK::setAccessToken(config('services.mercadopago.token'));
        
        $preapproval = \MercadoPago\Preapproval::find_by_id($dataId);
        
        if (!$preapproval) {
            throw new \Exception("Suscripción {$dataId} no encontrada en Mercado Pago.");
        }

        return [
            'external_reference' => $preapproval->external_reference, // Usualmente el ID del Studio
            'status' => $preapproval->status
        ];
    }
}