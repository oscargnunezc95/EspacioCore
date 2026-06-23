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
        // Detectamos si el estado implica perder el plan de pago
        // Mercado Pago envía 'paused' o 'cancelled'. Cubrimos 'free' por seguridad.
        $isDowngradeToFree = in_array(strtolower($this->status), ['paused', 'cancelled', 'free']);

        return [
            'type' => 'saas_subscription',
            'title' => $isDowngradeToFree ? 'Suscripción Detenida ⚠️' : 'Suscripción Activa 💎',
            'message' => $isDowngradeToFree 
                ? "Tu suscripción ha sido pausada o cancelada. Tu estudio ahora opera bajo el plan Gratis." 
                : "Tu suscripción ha sido procesada con éxito y tu plan está activo. ¡Gracias por confiar en nosotros!",
            'studio' => $this->studio->name,
            'icon' => $isDowngradeToFree ? 'error' : 'success'
        ];
    }
}