<?php

namespace App\Mail;

use App\Models\Studio;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentPaymentReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Studio $studio,
        public object $payment, // Objeto o array con los datos del pago
        public string $studentName
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recibo de pago - ' . $this->studio->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payments.student_receipt',
        );
    }
}