<?php

namespace Arkitecht\ScheduleFailureAlert\Providers;

use App\User;
use Illuminate\Support\ServiceProvider;

class ScheduleAlertsServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../../config/schedule-alerts.php' => config_path('schedule-alerts.php'),
        ]);

        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'courier');
    }
}
