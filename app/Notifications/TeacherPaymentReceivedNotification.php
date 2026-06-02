<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\TeacherPayment;

class TeacherPaymentReceivedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TeacherPayment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $mes = \Carbon\Carbon::parse($this->payment->month_year . '-01')->translatedFormat('F Y');

        return [
            'type'       => 'teacher_payment_received',
            'title'      => '¡Liquidación Recibida! 💰',
            'message'    => "El estudio {$this->payment->studio->name} te ha transferido $" . number_format($this->payment->amount, 0, ',', '.') . " por tu liquidación de {$mes}.",
            'studio'     => $this->payment->studio->name ?? 'Estudio',
            'payment_id' => $this->payment->id,
            'icon'       => 'success',
        ];
    }
}
