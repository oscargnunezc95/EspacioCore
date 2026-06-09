<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RemovedFromFamilyNotification extends Notification
{
    use Queueable;

    protected string $ownerName;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $ownerName)
    {
        $this->ownerName = $ownerName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'Vínculo Familiar Removido', // <-- AQUÍ ESTÁ LA SOLUCIÓN
            'message' => "{$this->ownerName} te ha removido de su grupo familiar. Ya no podrá gestionar tus clases.",
            'type'    => 'family_removed',
            'icon'    => 'user-minus',
            'color'   => 'rose'
        ];
    }
}