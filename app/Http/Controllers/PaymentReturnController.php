<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentReturnController extends Controller
{
    /**
     * Pago exitoso.
     *
     * Mercado Pago redirige aquí con parámetros por URL:
     * payment_id, status, external_reference, preference_id, etc.
     *
     * Determinamos el tipo de pago para mostrar la vista correcta:
     *  - platform_invoice_payment → factura de comisiones
     *  - student_payment          → reserva de clase/alumna
     */
    public function success(Request $request)
    {
        $paymentType = $this->resolvePaymentType($request);

        // Intentar extraer datos adicionales según el tipo de pago
        $meta = $this->decodeMeta($request);

        return view('payments.success', [
            'paymentId'   => $request->get('payment_id'),
            'status'      => $request->get('status'),
            'paymentType' => $paymentType,
            'meta'        => $meta,
        ]);
    }

    public function pending(Request $request)
    {
        return view('payments.pending');
    }

    public function failure(Request $request)
    {
        return view('payments.failure');
    }

    /**
     * Determina el tipo de pago desde el external_reference.
     * Fallback: 'student_payment' (comportamiento histórico).
     */
    private function resolvePaymentType(Request $request): string
    {
        $meta = $this->decodeMeta($request);

        return $meta['type'] ?? 'student_payment';
    }

    /**
     * Decodifica el external_reference JSON que Mercado Pago devuelve.
     */
    private function decodeMeta(Request $request): array
    {
        $raw = $request->get('external_reference');

        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
