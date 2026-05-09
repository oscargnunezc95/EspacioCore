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
        // Cada vez que Laravel vaya a renderizar la vista de navegación, ejecutará esto:
        View::composer('layouts.navigation', function ($view) {
            $portalBadgeCount = 0;
            
            if (Auth::check()) {
                $portalBadgeCount = Auth::user()->getUnpaidClassesCount();
            }

            // Inyectamos la variable limpia a la vista
            $view->with('portalBadgeCount', $portalBadgeCount);
        });
    }
}
