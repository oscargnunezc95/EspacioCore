<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class LocalProfileLinkedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $student;

    /**
     * Recibe la ficha del estudiante local recién vinculada.
     */
    public function __construct($student)
    {
        $this->student = $student;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Formato JSON que se guardará en la columna 'data' de la BD.
     */
    public function toArray($notifiable): array
    {
        $studio = $this->student->studio;

        return [
            'title' => '¡Cuenta unificada con éxito!',
            'message' => "El estudio \"{$studio->name}\" vinculó tu cuenta a su base de datos local. Ya puedes gestionar tus deudas y asistencias online.",
            'url' => route('global.payments.index'), // Redirección directa al Portal de Pagos
            'type' => 'profile_link',
            'studio_id' => $studio->id
        ];
    }
}