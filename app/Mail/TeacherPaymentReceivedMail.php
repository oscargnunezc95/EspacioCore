<?php

namespace App\Mail;

use App\Models\TeacherPayment;
use App\Models\Studio;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TeacherPaymentReceivedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public TeacherPayment $payment,
        public Studio $studio,
    ) {}

    public function envelope(): Envelope
    {
        $mes = \Carbon\Carbon::parse($this->payment->month_year . '-01')->translatedFormat('F Y');
        return new Envelope(
            subject: '💰 Liquidación recibida - ' . $this->studio->name . ' (' . $mes . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.payroll.teacher-payment-received',
        );
    }
}
