<?php

namespace App\Mail;

use App\Models\Studio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SubscriptionReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Studio $studio,
        public string $planName,
        public int $amount
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recibo de Suscripción - EstadoPrisma',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.subscriptions.receipt',
        );
    }
}