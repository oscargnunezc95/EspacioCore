<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class StudioMPUnlinkedNotification extends Notification
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
            'type' => 'mp_unlinked',
            'title' => 'Mercado Pago Desconectado ⚠️',
            // 👇 Inyectamos el nombre del estudio dinámicamente aquí 👇
            'message' => "Se ha desvinculado la pasarela de pagos de {$this->studio->name}. Las alumnas no podrán pagar de manera online hasta que la reconectes.",
            'studio' => $this->studio->name,
            'icon' => 'warning'
        ];
    }
}