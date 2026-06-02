<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;

class UserLinkedAsTeacherMail extends Mailable
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
            subject: '🧑‍🏫 Te han agregado como profesor en ' . $this->studio->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.onboarding.teacher-linked',
        );
    }
}
