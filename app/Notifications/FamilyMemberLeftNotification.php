<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class FamilyMemberLeftNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  string  $memberName  Nombre de la persona que abandonó el grupo familiar
     */
    public function __construct(
        public string $memberName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'    => 'family_member_left',
            'title'   => '🚪 Alguien salió de tu grupo familiar',
            'message' => "{$this->memberName} ha decidido salir de tu grupo familiar. Si tenía clases activas, estas han sido transferidas a su cuenta personal.",
            'studio'  => 'EstadoPrisma',
            'icon'    => 'warning',
        ];
    }
}
