<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use Teo\Chat\Http\Controllers\AdminController;
use Teo\Chat\Http\Controllers\ChatController;
use Teo\Chat\Http\Middleware\EnsureChatAdmin;

/*
|--------------------------------------------------------------------------
| teo/chat routes
|--------------------------------------------------------------------------
|
| 1. POST /api/broadcasting/auth — авторизация подписок Echo (Reverb).
| 2. /api/messages*             — публичное API виджета чата на сайте.
| 3. /api/admin/chat/*          — API панели «Сообщения» в wpadmin.
| 4. Broadcast-каналы           — strict-проверка доступа.
|
*/

// Авторизация broadcast-подключений (Echo → Reverb)
Route::post('/api/broadcasting/auth', function () {
    return Broadcast::auth(request());
})->middleware('auth:sanctum');

// Каналы вещания
Broadcast::channel('user.{id}', function ($user, $id) {
    $allowed = (int) $user->id === (int) $id
        || ($user->role ?? null) === config('chat.admin_role', 'admin');

    \Illuminate\Support\Facades\Log::channel('chatEvent')->debug('Channel auth', [
        'channel' => 'user.' . $id,
        'user_id' => $user->id,
        'allowed' => $allowed,
    ]);

    return $allowed;
});

Broadcast::channel('admin', function ($user) {
    return ($user->role ?? null) === config('chat.admin_role', 'admin');
});

// Публичный канал общего чата (legacy)
Broadcast::channel('chat', function () {
    return true;
});

// API виджета чата
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/api/messages', [ChatController::class, 'index']);
    Route::post('/api/send-message', [ChatController::class, 'send'])
        ->middleware('throttle:chat.send');
    Route::post('/api/messages/mark-read', [ChatController::class, 'markAsRead']);
    Route::get('/api/messages/unread-count', [ChatController::class, 'unreadCount']);

    // Админский API панели «Сообщения»
    Route::prefix('api/admin/chat')->middleware([EnsureChatAdmin::class])->group(function () {
        Route::get('/me', [AdminController::class, 'me']);
        Route::get('/stats', [AdminController::class, 'stats']);
        Route::get('/threads', [AdminController::class, 'threads']);
        Route::get('/threads/{userId}/messages', [AdminController::class, 'messages'])->whereNumber('userId');
        Route::post('/threads/{userId}/messages', [AdminController::class, 'send'])
            ->whereNumber('userId')
            ->middleware('throttle:chat.send');
        Route::post('/threads/{userId}/read', [AdminController::class, 'read'])->whereNumber('userId');
        Route::post('/users/{userId}/block', [AdminController::class, 'block'])->whereNumber('userId');
        Route::delete('/threads/{userId}', [AdminController::class, 'clear'])->whereNumber('userId');
    });
});