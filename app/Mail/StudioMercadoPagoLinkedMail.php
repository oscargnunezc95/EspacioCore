<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;

class StudioMercadoPagoLinkedMail extends Mailable implements ShouldQueue
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
            markdown: 'mail.studios.mp-linked',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}