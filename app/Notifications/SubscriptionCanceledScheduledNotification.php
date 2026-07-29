<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class SubscriptionCanceledScheduledNotification extends Notification
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
            'title'   => 'Cancelación Programada 📅',
            'message' => "Has solicitado cancelar tu suscripción. No se realizarán más cobros automáticos, pero mantendrás todos los beneficios Premium en \"{$this->studio->name}\" hasta el final de tu ciclo de facturación actual.",
            'studio'  => $this->studio->name,
            'icon'    => 'info',
        ];
    }
}
