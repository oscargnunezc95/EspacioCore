<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class SubscriptionCanceledImmediateNotification extends Notification
{
    use Queueable;

    public Studio $studio;

    public function __construct(Studio $studio)
    {
        $this->studio = $studio;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'saas_subscription',
            'title'   => 'Cancelación y Reembolso Emitido 🔄',
            'message' => "Has cancelado tu suscripción dentro del período de gracia. Tu plan en \"{$this->studio->name}\" ha vuelto a Gratuito de inmediato y se ha emitido el reembolso total a tu tarjeta.",
            'studio'  => $this->studio->name,
            'icon'    => 'success',
        ];
    }
}
