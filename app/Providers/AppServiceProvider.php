<?php

namespace App\Providers;

use App\Models\CarruselMensaje;
use App\Models\CarruselMovimiento;
use App\Observers\CarruselMensajeObserver;
use App\Observers\CarruselMovimientoObserver;
use Illuminate\Support\ServiceProvider;

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
        CarruselMovimiento::observe(CarruselMovimientoObserver::class);
        CarruselMensaje::observe(CarruselMensajeObserver::class);
    }
}
