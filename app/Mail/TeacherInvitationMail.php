<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;
use App\Models\Teacher;

class TeacherInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $studio;
    public $teacher;
    public $temporaryPassword;

    public function __construct(Studio $studio, Teacher $teacher, $temporaryPassword)
    {
        $this->studio = $studio;
        $this->teacher = $teacher;
        $this->temporaryPassword = $temporaryPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '👋 Has sido invitado como Profesor en ' . $this->studio->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.onboarding.teacher-invitation',
        );
    }
}