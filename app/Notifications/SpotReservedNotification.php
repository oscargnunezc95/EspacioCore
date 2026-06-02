<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ClassSession;

class SpotReservedNotification extends Notification
{
    use Queueable;

    public $session;
    public $availableSpots;

    /**
     * @param ClassSession $session  La sesión donde alguien reservó
     * @param int          $availableSpots  Cuántos cupos quedan ahora
     */
    public function __construct(ClassSession $session, int $availableSpots)
    {
        $this->session = $session;
        $this->availableSpots = $availableSpots;
    }

    /**
     * Solo notificación in-app (database). Sin correo.
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $workshopName = $this->session->workshop->name ?? 'la clase';
        $date = \Carbon\Carbon::parse($this->session->date)->format('d/m');
        $time = \Carbon\Carbon::parse($this->session->start_time)->format('H:i');

        $spotsText = $this->availableSpots === 1 
            ? '¡Solo queda 1 cupo!' 
            : "Quedan {$this->availableSpots} cupos";

        return [
            'type'    => 'spot_reserved',
            'title'   => "⚠️ {$spotsText}",
            'message' => "Alguien más se interesó en {$workshopName} del {$date} a las {$time}. Si no has pagado, asegura tu cupo cuanto antes.",
            'session_id' => $this->session->id,
            'icon'    => 'warning',
        ];
    }
}
