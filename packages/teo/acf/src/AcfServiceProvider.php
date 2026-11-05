<?php

namespace Teo\Acf;

use Illuminate\Support\ServiceProvider;
use Teo\Acf\Support\FieldGroupRegistry;
use Teo\Acf\Support\FieldGroupRepository;
use Teo\Acf\Support\FieldTypeRegistry;

class AcfServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Конфиг пакета (можно опубликовать тегом acf-config, но по умолчанию не копируется)
        $this->mergeConfigFrom(__DIR__ . '/../config/acf.php', 'acf');

        $this->app->singleton(FieldTypeRegistry::class, fn () => new FieldTypeRegistry());
        $this->app->singleton(FieldGroupRegistry::class, fn () => new FieldGroupRegistry());
        $this->app->singleton(FieldGroupRepository::class, fn () => new FieldGroupRepository());
    }

    public function boot(): void
    {
        // Миграции и маршруты грузятся непосредственно из пакета (без публикации/копирования)
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        // Гидрация реестра из БД. Важно: выполняется ПОСЛЕ boot() провайдеров приложения,
        // которые регистрируют группы через Acf::addGroup() (порядок bootstrap/providers.php).
        $this->app->booted(function () {
            $this->app->make(FieldGroupRepository::class)
                ->hydrate($this->app->make(FieldGroupRegistry::class));
        });

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/acf.php' => config_path('acf.php'),
            ], 'acf-config');

            $this->commands([
                Commands\AcfMakeGroupCommand::class,
            ]);
        }
    }
}
