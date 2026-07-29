<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class SubscriptionCanceledByDebtNotification extends Notification
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
            'title'   => 'Suscripción Cancelada por Morosidad ❌',
            'message' => "Han transcurrido los 5 días de gracia sin recibir el pago. Tu suscripción en \"{$this->studio->name}\" ha sido cancelada y tu estudio ha vuelto al Plan Gratuito. No has perdido ningún dato.",
            'studio'  => $this->studio->name,
            'icon'    => 'error',
        ];
    }
}
