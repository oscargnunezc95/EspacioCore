<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\UserDependent;

class DependentTransferRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $currentOwner,        // Quien tiene al dependiente actualmente
        public User $requester,           // Quien quiere la transferencia
        public UserDependent $dependent,  // El dependiente en disputa
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔄 ' . $this->requester->name . ' solicita la transferencia de ' . $this->dependent->first_name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.family.transfer-request',
        );
    }
}
