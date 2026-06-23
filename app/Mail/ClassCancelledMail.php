<?php

namespace App\Mail;

use App\Models\ClassSession;
use App\Models\Studio;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ClassCancelledMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public ClassSession $session;
    public Studio $studio;

    public function __construct(ClassSession $session, Studio $studio)
    {
        $this->session = $session;
        $this->studio = $studio;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Aviso Importante: Clase Cancelada en {$this->studio->name}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.class-cancelled',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}