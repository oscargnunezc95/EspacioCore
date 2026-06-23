<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;
use App\Models\Student;

class StudentInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $studio;
    public $student;

    public function __construct(Studio $studio, Student $student)
    {
        $this->studio = $studio;
        $this->student = $student;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 Te han agregado como alumno en ' . $this->studio->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.onboarding.student-invitation',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
