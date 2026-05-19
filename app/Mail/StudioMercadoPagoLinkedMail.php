<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;

class StudioMercadoPagoLinkedMail extends Mailable
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
            subject: '✅ Tu cuenta de Mercado Pago ha sido vinculada',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.studios.mp-linked',
        );
    }
}