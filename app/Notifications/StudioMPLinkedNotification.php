<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class StudioMPLinkedNotification extends Notification
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
            'type' => 'mp_linked',
            'title' => 'Mercado Pago Conectado',
            // 👇 Inyectamos el nombre del estudio dinámicamente aquí 👇
            'message' => "La cuenta de Mercado Pago se vinculó con éxito a {$this->studio->name}. Ya puedes recibir pagos online de tus alumnas.",
            'studio' => $this->studio->name,
            'icon' => 'success'
        ];
    }
}