<?php

namespace App\Mail;

use App\Models\Studio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudioPaymentNotificationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Studio $studio,
        public object $payment,
        public string $studentName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📥 Pago Recibido - ' . $this->studentName,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payments.studio_notification',
        );
    }
}