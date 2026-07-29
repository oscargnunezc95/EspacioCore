<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ClassSession;

class SpotsDecreasedNotification extends Notification
{
    use Queueable;

    public $session;
    public $availableSpots;

    /**
     * Se envía cuando alguien PAGA una clase (detectado por webhook).
     * Se notifica a las personas interesadas que aún no han pagado,
     * avisando que los cupos disponibles bajaron.
     *
     * @param ClassSession $session        La sesión donde alguien pagó
     * @param int          $availableSpots Cuántos cupos quedan ahora
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
            'type'    => 'spots_decreased',
            'title'   => "📉 {$spotsText}",
            'message' => "Alguien acaba de pagar su cupo en {$workshopName} del {$date} a las {$time}. Ahora {$spotsText} disponibles. ¡No te quedes fuera!",
            'session_id' => $this->session->id,
            'icon'    => 'warning',
        ];
    }
}
