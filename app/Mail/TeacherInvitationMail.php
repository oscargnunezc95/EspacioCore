<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;
use App\Models\Teacher;

class TeacherInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $studio;
    public $teacher;
    public $temporaryPassword;

    /**
     * @param Studio       $studio
     * @param Teacher      $teacher
     * @param string|null  $temporaryPassword  Null si es invitación huérfana (sin User creado)
     */
    public function __construct(Studio $studio, Teacher $teacher, $temporaryPassword = null)
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
