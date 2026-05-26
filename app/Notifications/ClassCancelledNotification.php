<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ClassSession;
use App\Models\Studio;

class ClassCancelledNotification extends Notification
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
        return ['database']; // Solo in-app, el correo va por BCC en el controlador
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'cancelled',
            'title'      => 'Clase Cancelada',
            'message'    => "Tu clase de {$this->session->workshop->name} el " . \Carbon\Carbon::parse($this->session->date)->translatedFormat('d/m') . " ha sido cancelada.",
            'studio'     => $this->studio->name,
            'session_id' => $this->session->id,
            'icon'       => 'error' // Para pintar la campanita de rojo
        ];
    }
}