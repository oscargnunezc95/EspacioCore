<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class SubscriptionPastDueNotification extends Notification
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
            'title'   => 'Pago Pendiente ⚠️',
            'message' => "El cobro automático de tu suscripción no se ha podido procesar. Tu estudio \"{$this->studio->name}\" está en período de gracia. Tienes 5 días para regularizar antes del downgrade automático al plan gratuito.",
            'studio'  => $this->studio->name,
            'icon'    => 'warning',
        ];
    }
}
