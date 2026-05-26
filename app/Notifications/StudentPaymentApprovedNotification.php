<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class StudentPaymentApprovedNotification extends Notification
{
    use Queueable;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_approved',
            'title' => '¡Pago Confirmado! 🎉',
            'message' => "Tu pago de $" . number_format($this->payment->amount, 0, ',', '.') . " ha sido procesado. Tus cupos están asegurados.",
            'studio' => $this->payment->studio->name ?? 'Estudio',
            'payment_id' => $this->payment->id,
            'icon' => 'success'
        ];
    }
}