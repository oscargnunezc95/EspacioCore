<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail; // <-- Extendemos de la nativa de Laravel

class QueuedVerifyEmail extends VerifyEmail implements ShouldQueue
{
    use Queueable;

    // Al extender VerifyEmail y usar ShouldQueue, Laravel hereda 
    // todo el diseño y el link firmado del correo original, 
    // pero ahora sabe que debe mandarlo en segundo plano.
}