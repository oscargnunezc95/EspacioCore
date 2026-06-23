<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LocalProfileLinkedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $student;
    public $studio;

    public function __construct($student)
    {
        $this->student = $student;
        $this->studio = $student->workshop->studio ?? $student->studio;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tu perfil ha sido unificado con {$this->studio->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.local-profile-linked',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}