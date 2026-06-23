<?php

namespace App\Mail;

use App\Models\ClassSession;
use App\Models\Student;
use App\Models\Studio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassRefundedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public ClassSession $session,
        public Student $student,
        public Studio $studio,
        public float $refundedAmount,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Reembolso procesado — {$this->session->workshop->name} en {$this->studio->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.class-refunded',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
