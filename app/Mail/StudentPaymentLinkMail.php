<?php

namespace App\Mail;

use App\Models\Studio;
use App\Models\Student;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StudentPaymentLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Studio $studio,
        public Student $student,
        public string $paymentLink,
        public float $totalAmount,
        public string $paymentType = 'single',
        public int $classCount = 1,
        public array $breakdown = [],
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->paymentType) {
            'promocion' => '🌟 Promoción especial - ' . $this->studio->name,
            'pack'      => '📦 Pack de ' . $this->classCount . ' clases - ' . $this->studio->name,
            default     => 'Link de pago - ' . $this->studio->name,
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.payments.payment_link',
        );
    }
}
