<?php

namespace Teo\Admin;

use Illuminate\Support\ServiceProvider;
use Teo\Admin\Http\Middleware\AdminMiddleware;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/admin.php', 'admin');
    }

    public function boot(): void
    {
        // Роуты админки: публичный вход/регистрация, защищённый API (auth:sanctum)
        // и SPA-fallback /admin/{any?} (материализуется раньше публичного
        // Route::fallback из teo/pages, т.к. явный маршрут всегда приоритетнее fallback).
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        // Blade-оболочка SPA: view('admin::app')
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'admin');

        // Алиас middleware 'admin' (проверка role === admin)
        $this->app['router']->aliasMiddleware('admin', AdminMiddleware::class);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/admin.php' => config_path('admin.php'),
            ], 'admin-config');
        }
    }
}
