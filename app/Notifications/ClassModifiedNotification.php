<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ClassSession;
use App\Models\Studio;

class ClassModifiedNotification extends Notification
{
    use Queueable;

    public $session;
    public $studio;

    public function __construct(ClassSession $session, Studio $studio)
    {
        $this->session = $session;
        $this->studio = $studio;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'modified',
            'title'      => 'Clase Modificada',
            'message'    => "Tu clase de {$this->session->workshop->name} ha sufrido cambios de horario o profesor.",
            'studio'     => $this->studio->name,
            'session_id' => $this->session->id,
            'icon'       => 'info' // Para pintar la campanita de azul
        ];
    }
}