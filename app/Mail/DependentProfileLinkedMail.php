<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DependentProfileLinkedMail extends Mailable implements ShouldQueue
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
            subject: "Control Familiar: Ficha unificada para {$this->student->first_name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.dependent-profile-linked',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}