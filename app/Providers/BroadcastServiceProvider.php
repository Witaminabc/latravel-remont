<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // ✅ ВАЖНО: добавляем middleware auth:sanctum
        Broadcast::routes(['middleware' => ['auth:sanctum']]);

        // Загружаем каналы
        require base_path('routes/channels.php');
    }
}
