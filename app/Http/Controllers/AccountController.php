<?php

namespace App\Http\Controllers;

use App\Models\Studio;
use App\Models\Payment;
use App\Models\StudioInvoice;
use App\Services\BillingService;
use App\Services\MercadoPagoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\StudioMercadoPagoUnlinkedMail;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient;

class AccountController extends Controller
{
    /**
     * Pestaña principal: Pagos recientes (existente).
     */
    public function index($subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $payments = Payment::with('student')
            ->whereHas('workshop', function ($query) use ($studio) {
                $query->where('studio_id', $studio->id);
            })
            ->latest()
            ->paginate(15);

        // Proyección del mes en curso para mostrar en la UI
        $projection = app(BillingService::class)->getCurrentMonthProjection($studio);

        // Últimas facturas
        $invoices = $studio->invoices()->latest('billing_period')->take(12)->get();

        return view('account.index', compact('studio', 'payments', 'subdomain', 'projection', 'invoices'));
    }

    /**
     * Pestaña de Facturación: panel de control completo.
     */
    public function billing($subdomain, BillingService $billingService)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        $projection = $billingService->getCurrentMonthProjection($studio);
        $invoices = $studio->invoices()->latest('billing_period')->paginate(12); 

        return view('account.billing.index', compact('studio', 'subdomain', 'projection', 'invoices'));
    }

    /**
     * Genera una preferencia de pago de Mercado Pago para pagar una factura
     * de plataforma. El cobro se hace con el token GLOBAL de la plataforma
     * (no con el token del estudio).
     */
    public function payInvoice($subdomain, $invoiceId)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();
        $invoice = $studio->invoices()->findOrFail($invoiceId);

        if ($invoice->isPaid()) {
            return back()->with('error', 'Esta factura ya fue pagada.');
        }

        // Usar el token GLOBAL de la plataforma para cobrar la comisión
        $platformToken = config('services.mercadopago.platform_token')
            ?: config('services.mercadopago.token');

        if (empty($platformToken)) {
            Log::critical('MERCADOPAGO_PLATFORM_TOKEN no configurado. No se puede cobrar factura de plataforma.');
            return back()->with('error', 'El sistema de pagos no está disponible. Contacta a soporte.');
        }

        MercadoPagoConfig::setAccessToken($platformToken);

        $baseUrl = rtrim(config('app.url'), '/');
        $webhookDomain = config('services.mercadopago.webhook_domain') ?: $baseUrl;

        $request = [
            'items' => [
                [
                    'title'       => "Comisión EstadoPrisma — {$invoice->billing_period}",
                    'quantity'    => 1,
                    'unit_price'  => (float) $invoice->total_due,
                    'currency_id' => $studio->currency_code,
                ],
            ],
            'statement_descriptor' => 'ESTADOPRISMA',
            'external_reference'   => json_encode([
                'type'       => 'platform_invoice_payment',
                'invoice_id' => $invoice->id,
                'studio_id'  => $studio->id,
            ]),
            'back_urls' => [
                'success' => $baseUrl . '/pagos/exito',
                'failure' => $baseUrl . '/pagos/error',
                'pending' => $baseUrl . '/pagos/pendiente',
            ],
            'auto_return'      => 'approved',
            'notification_url' => rtrim($webhookDomain, '/') . '/api/webhooks/mercadopago',
        ];

        try {
            $client = new PreferenceClient();
            $preference = $client->create($request);

            if (!$preference->init_point) {
                throw new \Exception('No se pudo generar el link de pago.');
            }

            Log::info("🔗 Preferencia de pago de factura generada: factura #{$invoice->id}, studio #{$studio->id}");

            return redirect()->away($preference->init_point);
        } catch (\Exception $e) {
            Log::error('Error generando preferencia de pago de factura: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'studio_id'  => $studio->id,
            ]);
            return back()->with('error', 'Error al generar el link de pago: ' . $e->getMessage());
        }
    }

    /**
     * Configura (o re-configura) el QR estático de MercadoPago para el estudio.
     */
    public function setupStaticQR($subdomain)
    {
        $studio = Studio::where('subdomain', $subdomain)->firstOrFail();

        if (empty($studio->mp_access_token)) {
            return back()->with('error', 'Primero debes vincular tu cuenta de MercadoPago desde la sección Mi Cuenta.');
        }

        try {
            app(\App\Services\MercadoPagoService::class)->setupStaticQR($studio);
            return back()->with('success', '¡QR estático configurado exitosamente! Tus alumnas ya pueden pagar escaneando el QR de tu estudio.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al configurar el QR: ' . $e->getMessage());
        }
    }

    /**
     * Desvincula la cuenta de Mercado Pago del estudio.
     */
    public function disconnectMercadoPago(Request $request)
    {
        $studio = auth()->user()->studios()->first();

        if ($studio) {
            $studio->update([
                'mp_access_token'    => null,
                'mp_refresh_token'   => null,
                'mp_user_id'         => null,
                'mp_store_id'        => null,
                'mp_external_pos_id' => null,
                'mp_pos_qr_url'      => null,
            ]);

            Log::info("El estudio {$studio->name} ha desvinculado su cuenta de Mercado Pago.");

            try {
                if ($studio->user) {
                    if ($studio->user->email) {
                        Mail::to($studio->user->email)->send(new StudioMercadoPagoUnlinkedMail($studio));
                    }
                    $studio->user->notify(new \App\Notifications\StudioMPUnlinkedNotification($studio));
                }
            } catch (\Exception $e) {
                Log::error('Se desvinculó MP, pero fallaron las alertas: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Tu cuenta de Mercado Pago ha sido desvinculada correctamente.');
    }
}
