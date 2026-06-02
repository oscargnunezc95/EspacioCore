<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\UserDependent;

class FamilyLinkRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $targetUser,          // El usuario global al que se quiere agregar como familiar
        public User $requester,           // Quien hace la solicitud
        public UserDependent $dependent,  // El dependiente creado
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '👨‍👩‍👧 ' . $this->requester->name . ' te ha agregado como familiar en EstadoPrisma',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.family.link-request',
        );
    }
}
