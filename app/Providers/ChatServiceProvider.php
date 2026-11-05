<?php
// app/Providers/ChatServiceProvider.php
namespace App\Providers;
use Illuminate\Support\ServiceProvider;

//use App\Services\Chat\ChatService;
//use App\Services\Chat\MessageNotifier;
//use App\Repositories\MessageRepository;


class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
// Регистрируем сервисы чата
//        $this->app->singleton(ChatService::class, function ($app) {
//            return new ChatService(
//                $app->make(MessageRepository::class),
//                $app->make(MessageNotifier::class)
//            );
//        });

// Регистрируем события
//        $this->app->singleton('chat.events', function () {
//            return new EventDispatcher();
//        });
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
// Загружаем маршруты для WebSocket
//        if ($this->app->runningInConsole()) {
//            $this->commands([
//                \App\Console\Commands\StartWebSocket::class,
//            ]);
//        }

// Публикуем конфиг
//        $this->publishes([
//            __DIR__ . '/../../config/chat.php' => config_path('chat.php'),
//        ]);
    }
}
