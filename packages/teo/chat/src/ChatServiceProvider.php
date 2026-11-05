<?php

namespace Teo\Chat;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Teo\Chat\Events\MessageSent;
use Teo\Chat\Listeners\NotifyAdminNewMessage;

class ChatServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/chat.php', 'chat');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/Http/routes.php');

        // Email-уведомление админу о новых сообщениях чата (config chat.notify)
        Event::listen(MessageSent::class, NotifyAdminNewMessage::class);

        // Именованный лимитер отправки сообщений (chat.send)
        RateLimiter::for('chat.send', function (Request $request) {
            $key = $request->user()?->id ?? $request->ip();

            return Limit::perMinute((int) config('chat.throttle.send_per_minute', 30))
                ->by('chat-send:' . $key);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\InstallChatCommand::class,
            ]);

            $this->publishes([
                __DIR__ . '/../config/chat.php' => config_path('chat.php'),
            ], 'chat-config');

            $this->publishes([
                __DIR__ . '/Database/Migrations' => database_path('migrations'),
            ], 'chat-migrations');

            $this->publishes([
                __DIR__ . '/Resources/views/chat.blade.php.stub' => resource_path('views/chat.blade.php'),
            ], 'chat-views');

            $this->publishes([
                __DIR__ . '/Resources/js' => resource_path('js/chat'),
            ], 'chat-js');

            $this->publishes([
                __DIR__ . '/Resources/sass' => resource_path('sass/chat'),
            ], 'chat-styles');
        }
    }
}