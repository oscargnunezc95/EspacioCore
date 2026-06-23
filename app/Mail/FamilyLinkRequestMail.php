<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use App\Models\User;
use App\Models\UserDependent;

class FamilyLinkRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public string $acceptUrl;
    public string $rejectUrl;

    public function __construct(
        public User $targetUser,          // El usuario global al que se quiere agregar como familiar
        public User $requester,           // Quien hace la solicitud
        public UserDependent $dependent,  // El dependiente creado
    ) {
        $this->acceptUrl = URL::temporarySignedRoute(
            'profile.family.accept',
            now()->addDays(7),
            ['dependent' => $this->dependent->id]
        );
        $this->rejectUrl = URL::temporarySignedRoute(
            'profile.family.reject',
            now()->addDays(7),
            ['dependent' => $this->dependent->id]
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 ' . $this->requester->name . ' solicita administrar tus clases en EstadoPrisma',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.family.link-request',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
