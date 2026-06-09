<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;

class StudioMercadoPagoUnlinkedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $studio;

    public function __construct(Studio $studio)
    {
        $this->studio = $studio;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Se ha desvinculado tu cuenta de Mercado Pago',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.studios.mp-unlinked',
        );
    }
}