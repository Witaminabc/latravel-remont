<?php

namespace Teo\Pages;

use Illuminate\Support\ServiceProvider;
use Teo\Pages\Support\SeoBuilder;
use Teo\Pages\Support\SeoManager;
use Teo\Pages\Support\TemplateRegistry;

class PagesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/pages.php', 'pages');

        $this->app->singleton(TemplateRegistry::class);
        $this->app->singleton(SeoManager::class);
        $this->app->singleton(SeoBuilder::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/pages.php' => config_path('pages.php'),
            ], 'pages-config');
        }
    }
}