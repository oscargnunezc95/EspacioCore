<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\Payment;

class StudentPaymentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Define los canales de entrega de la notificación.
     * Al dejar solo 'database', evitamos que Laravel dispare un segundo correo duplicado.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Representación para la base de datos (campana de notificaciones in-app).
     */
    public function toArray(object $notifiable): array
    {
        $studioName = $this->payment->studio->name ?? 'Estudio';
        $amountFormatted = number_format($this->payment->amount, 0, ',', '.');

        // Contar clases para el resumen in-app
        $classCount = $this->payment->classSessions()->count();

        return [
            'type'       => 'payment_approved',
            'title'      => '¡Pago Confirmado! 🎉',
            'message'    => $classCount > 0
                ? "Tu pago de \${$amountFormatted} por {$classCount} clase(s) en {$studioName} ha sido procesado. Tus cupos están asegurados."
                : "Tu pago de \${$amountFormatted} en {$studioName} ha sido procesado. Tus cupos están asegurados.",
            'studio'     => $studioName,
            'payment_id' => $this->payment->id,
            'icon'       => 'success',
        ];
    }
}