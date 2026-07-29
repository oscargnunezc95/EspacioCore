<?php

namespace App\Mail;

use App\Models\Studio;
use App\Models\StudioInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudioInvoiceGeneratedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Studio $studio,
        public StudioInvoice $invoice,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "🧾 Factura Mensual — {$this->invoice->billing_period} | EstadoPrisma",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.billing.invoice-generated',
        );
    }
}
