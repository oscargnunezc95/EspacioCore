<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;

class UserLinkedToStudioMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $studio;
    public $userName;

    public function __construct(Studio $studio, string $userName)
    {
        $this->studio = $studio;
        $this->userName = $userName;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🏢 Nuevo estudio vinculado a tu cuenta - ' . $this->studio->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.onboarding.user-linked',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}