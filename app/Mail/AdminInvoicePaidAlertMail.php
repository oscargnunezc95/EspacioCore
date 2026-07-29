<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;
use App\Models\StudioInvoice;

class AdminInvoicePaidAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $studio;
    public $invoice;

    public function __construct(Studio $studio, StudioInvoice $invoice)
    {
        $this->studio = $studio;
        $this->invoice = $invoice;
    }

    public function build()
    {
        $studioName = $this->studio->name ?? 'Estudio';
        $amount = number_format($this->invoice->total_due, 0, ',', '.');
        $period = $this->invoice->billing_period;

        return $this->subject("✅ [COMISIÓN PAGADA] {$studioName} pagó su factura de {$period} (\${$amount})")
                    ->markdown('mail.admin.invoice-paid-alert'); // ✅ Solucionado
    }
}