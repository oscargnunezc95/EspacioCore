<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Studio;
use App\Models\Student;

class StudentAddedNotification extends Notification
{
    use Queueable;

    public Studio $studio;
    public Student $student;

    public function __construct(Studio $studio, Student $student)
    {
        $this->studio = $studio;
        $this->student = $student;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isSelf = ($this->student->user_id === $notifiable->id);

        if ($isSelf) {
            return [
                'type' => 'student_added',
                'title' => '¡Bienvenida al Estudio! 🎉',
                'message' => "Tu ficha de alumna en {$this->studio->name} está lista. Revisa tus próximas clases desde el portal.",
                'studio' => $this->studio->name,
                'icon' => 'success'
            ];
        }

        return [
            'type' => 'dependent_added',
            'title' => 'Familiar Inscrito 👨‍👩‍👧',
            'message' => "Se ha agregado al familiar {$this->student->first_name} como estudiante en {$this->studio->name}.",
            'studio' => $this->studio->name,
            'icon' => 'info'
        ];
    }
}