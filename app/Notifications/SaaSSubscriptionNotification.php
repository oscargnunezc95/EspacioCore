<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;

class SaaSSubscriptionNotification extends Notification
{
    use Queueable;

    public $studio;
    public $status;

    public function __construct(Studio $studio, string $status)
    {
        $this->studio = $studio;
        $this->status = $status;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isPro = ($this->status === 'authorized');

        return [
            'type' => 'saas_subscription',
            'title' => $isPro ? 'Suscripción Renovada 💎' : 'Suscripción Cancelada o Pausada',
            'message' => $isPro 
                ? "Tu suscripción Pro ha sido procesada con éxito. ¡Gracias por seguir confiando en nosotros!" 
                : "Tu plan ha cambiado al estado: {$this->status}. Revisa tus opciones de facturación.",
            'studio' => $this->studio->name,
            'icon' => $isPro ? 'success' : 'error'
        ];
    }
}