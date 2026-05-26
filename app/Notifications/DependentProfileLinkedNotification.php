<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class DependentProfileLinkedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $student;

    /**
     * Recibe la ficha del familiar local vinculada.
     */
    public function __construct($student)
    {
        $this->student = $student;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $studio = $this->student->studio;

        return [
            'title' => 'Ficha familiar vinculada',
            'message' => "La ficha local de tu familiar {$this->student->first_name} en \"{$studio->name}\" ha sido enlazada a tu billetera titular.",
            'url' => route('global.payments.index'),
            'type' => 'family_link',
            'studio_id' => $studio->id
        ];
    }
}