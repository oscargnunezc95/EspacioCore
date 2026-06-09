<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;
use App\Models\Student;

class StudentWelcomeMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $studio;
    public $student;
    public $temporaryPassword;

    public function __construct(Studio $studio, Student $student, $temporaryPassword)
    {
        $this->studio = $studio;
        $this->student = $student;
        $this->temporaryPassword = $temporaryPassword;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✨ ¡Te damos la bienvenida a ' . $this->studio->name . '!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.onboarding.student-welcome',
        );
    }
}