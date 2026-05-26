<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class StudentAddedNotification extends Notification
{
    use Queueable;

    public $studio;

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
            'type' => 'student_added',
            'title' => '¡Bienvenida al Estudio! 🎉',
            'message' => "Tu ficha de alumna en {$this->studio->name} está lista. Revisa tus próximas clases desde el portal.",
            'studio' => $this->studio->name,
            'icon' => 'success'
        ];
    }
}