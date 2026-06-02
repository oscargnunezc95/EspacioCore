<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\ClassSession;

class ClassFullNotification extends Notification
{
    use Queueable;

    public $session;

    public function __construct(ClassSession $session)
    {
        $this->session = $session;
    }

    /**
     * Email + in-app notification.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Email para cuando la clase se llena.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $workshopName = $this->session->workshop->name ?? 'la clase';
        $date = \Carbon\Carbon::parse($this->session->date)->translatedFormat('l d \\d\\e F');
        $time = \Carbon\Carbon::parse($this->session->start_time)->format('H:i');
        $studioName = $this->session->workshop->studio->name ?? 'el estudio';

        return (new MailMessage)
            ->subject("🚨 ¡Sin cupos! {$workshopName} — Actúa ahora")
            ->greeting('¡Hola!')
            ->line("La clase **{$workshopName}** en **{$studioName}** del **{$date} a las {$time} hrs** acaba de quedarse sin cupos disponibles.")
            ->line("Como tenías una reserva pendiente de pago, tu lugar **no está asegurado**. Si alguien paga antes que tú, podrías perder tu cupo.")
            ->action('Ir a Pagar Ahora', route('cart.index'))
            ->line('¡No te quedes fuera! Asegura tu lugar completando el pago lo antes posible.');
    }

    /**
     * Notificación in-app.
     */
    public function toArray(object $notifiable): array
    {
        $workshopName = $this->session->workshop->name ?? 'la clase';
        $date = \Carbon\Carbon::parse($this->session->date)->format('d/m');
        $time = \Carbon\Carbon::parse($this->session->start_time)->format('H:i');

        return [
            'type'    => 'class_full',
            'title'   => '🚨 ¡Clase llena!',
            'message' => "{$workshopName} del {$date} a las {$time} ya no tiene cupos. Si no has pagado, ¡apresúrate!",
            'session_id' => $this->session->id,
            'icon'    => 'danger',
        ];
    }
}
