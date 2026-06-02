<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Workshop;
use App\Models\Studio;

class WorkshopAssignedNotification extends Notification
{
    use Queueable;

    public $workshop;
    public $studio;

    public function __construct(Workshop $workshop, Studio $studio)
    {
        $this->workshop = $workshop;
        $this->studio = $studio;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = route('login');

        return (new MailMessage)
            ->subject('📋 Nuevo taller asignado: ' . $this->workshop->name . ' - ' . $this->studio->name)
            ->greeting('¡Hola, ' . explode(' ', $notifiable->name ?? 'Profesor')[0] . '!')
            ->line('El estudio **' . $this->studio->name . '** te ha asignado un nuevo taller.')
            ->line('**' . $this->workshop->name . '**')
            ->line('Ya puedes revisar los detalles y las alumnas inscritas desde tu Portal de Profesor.')
            ->action('Ingresar a mi Portal', $loginUrl)
            ->salutation('Saludos, ' . $this->studio->name);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'workshop_assigned',
            'title' => 'Nuevo Taller Asignado 📋',
            'message' => "Se te ha asignado el taller \"{$this->workshop->name}\" en {$this->studio->name}. Revisa los detalles en tu portal.",
            'workshop_id' => $this->workshop->id,
            'workshop_name' => $this->workshop->name,
            'studio' => $this->studio->name,
            'icon' => 'calendar'
        ];
    }
}
