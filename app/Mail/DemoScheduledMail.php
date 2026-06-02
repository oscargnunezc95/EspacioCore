<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DemoScheduledMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $date,
        public string $time,
        public string $jitsiLink,
        public ?string $email = null,
        public ?string $body = null,
        public bool $isAdminCopy = false,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isAdminCopy
            ? '📹 Demo agendada: ' . $this->name
            : '✅ Tu demo con EstadoPrisma está agendada';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: $this->isAdminCopy
                ? 'mail.support.demo-scheduled-admin'
                : 'mail.support.demo-scheduled-user',
        );
    }
}
