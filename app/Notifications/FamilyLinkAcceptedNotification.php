<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;

class FamilyLinkAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  User  $acceptor  El usuario que aceptó el vínculo
     */
    public function __construct(
        public User $acceptor,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'family_link_accepted',
            'title'   => '✅ Vínculo familiar aceptado',
            'message' => "{$this->acceptor->name} ha aceptado tu solicitud y ahora es parte de tu familia. Ya puedes inscribirle en clases y gestionar sus reservas.",
            'studio'  => 'EstadoPrisma',
            'icon'    => 'success',
        ];
    }
}
