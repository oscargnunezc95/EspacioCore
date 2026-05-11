<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

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
    }
}
