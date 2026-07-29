<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\ClassSession;

class SpotReservedNotification extends Notification
{
    use Queueable;

    public $session;
    public $interestedCount;

    /**
     * Se envía ÚNICAMENTE cuando alguien nuevo se interesa en una clase (add to cart).
     * Se notifica a las otras personas interesadas que aún no han pagado,
     * mostrando cuántas personas están interesadas en total.
     *
     * @param ClassSession $session         La sesión donde alguien se interesó
     * @param int          $interestedCount  Cuántas personas están interesadas (pending) en total
     */
    public function __construct(ClassSession $session, int $interestedCount)
    {
        $this->session = $session;
        $this->interestedCount = $interestedCount;
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

        $peopleText = $this->interestedCount === 1
            ? '1 persona está interesada'
            : "{$this->interestedCount} personas están interesadas";

        return [
            'type'    => 'spot_reserved',
            'title'   => "👀 {$peopleText}",
            'message' => "Alguien más se interesó en {$workshopName} del {$date} a las {$time}. ¡{$peopleText} en total! Si no has pagado, asegura tu cupo cuanto antes.",
            'session_id' => $this->session->id,
            'icon'    => 'info',
        ];
    }
}
