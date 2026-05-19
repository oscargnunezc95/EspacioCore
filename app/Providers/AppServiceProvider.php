<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Ampliamos el "radar" para que la variable llegue sin importar 
        // cómo se llame exactamente el archivo de tu navbar
        View::composer(
            ['layouts.navigation', 'layouts.app', 'components.navigation', 'navigation'], 
            function ($view) {
                $count = 0;
                if (Auth::check()) {
                    $count = Auth::user()->pending_reservations_count;
                }
                $view->with('portalBadgeCount', $count);
            }
        );
        // Personalización del correo de verificación
        VerifyEmail::toMailUsing(function (object $notifiable, string $url) {
            return (new MailMessage)
                ->subject('Verifica tu acceso a EstadoPrisma')
                ->greeting('¡Hola, ' . $notifiable->name . '!')
                ->line('Gracias por dar el paso y crear tu cuenta en EstadoPrisma. Para asegurar la información de tu academia, por favor verifica tu correo electrónico.')
                ->action('Verificar mi cuenta', $url)
                ->line('Si no te registraste en nuestra plataforma, puedes ignorar este correo sin problemas.')
                ->salutation('El equipo de ' . config('app.name'));
        });
    }
}
