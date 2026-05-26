<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class TeacherAddedNotification extends Notification
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
            'type' => 'teacher_added',
            'title' => 'Nuevo Espacio de Trabajo 📋',
            'message' => "Has sido asignado como profesor/a en el staff de {$this->studio->name}. Ya puedes revisar tus clases programadas.",
            'studio' => $this->studio->name,
            'icon' => 'success'
        ];
    }
}