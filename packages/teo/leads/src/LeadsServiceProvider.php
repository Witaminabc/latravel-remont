<?php

namespace Teo\Leads;

use Illuminate\Support\ServiceProvider;

class LeadsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/leads.php', 'leads');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/leads.php' => config_path('leads.php'),
        ], 'leads-config');

        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'leads');
    }
}
