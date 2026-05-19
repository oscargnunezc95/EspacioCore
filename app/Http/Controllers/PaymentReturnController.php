<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentReturnController extends Controller
{
    public function success(Request $request)
    {
        // Mercado Pago nos manda datos por la URL (payment_id, status, external_reference, etc.)
        // En una arquitectura robusta, NO confiamos ciegamente en esto para marcar la clase 
        // como pagada (eso lo hará el Webhook), pero sí lo usamos para mostrar un mensaje de éxito.
        
        return view('payments.success', [
            'paymentId' => $request->get('payment_id'),
            'status' => $request->get('status')
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
}