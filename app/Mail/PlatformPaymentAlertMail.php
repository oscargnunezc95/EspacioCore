<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Studio;
use App\Models\Payment;

class PlatformPaymentAlertMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $studio;
    public $payment;
    public $studentName;

    public function __construct(Studio $studio, Payment $payment, string $studentName)
    {
        $this->studio = $studio;
        $this->payment = $payment;
        $this->studentName = $studentName;
    }

    public function build()
    {
        $studioName = $this->studio->name ?? 'Estudio Desconocido';
        $amount = number_format($this->payment->amount, 0, ',', '.');
        $currency = $this->studio->currency_symbol ?? '$';

        return $this->subject("💰 [NUEVA VENTA] {$this->studentName} compró en {$studioName} ({$currency}{$amount})")
                    ->view('mail.admin.payment-alert');
    }
}