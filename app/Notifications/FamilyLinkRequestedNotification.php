<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Models\User;

class FamilyLinkRequestedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  User  $requester  El apoderado que solicita el vínculo
     */
    public function __construct(
        public User $requester,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'family_link_requested',
            'title'   => '📋 Solicitud de vínculo familiar',
            'message' => "{$this->requester->name} quiere administrar tus clases en EstadoPrisma. Revisa tu correo o tu perfil para aceptar o rechazar.",
            'studio'  => 'EstadoPrisma',
            'icon'    => 'info',
        ];
    }
}
