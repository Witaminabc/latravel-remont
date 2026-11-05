<?php

use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth-маршруты чата (нужны wpadmin)
|--------------------------------------------------------------------------
|
| API виджета (/api/messages*), админский API (/api/admin/chat/*),
| broadcast-каналы и /api/broadcasting/auth регистрирует пакет teo/chat
| (packages/teo/chat/src/Http/routes.php).
|
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // Auth (используется и wpadmin, и чатом)
    Route::get('/api/user', [AuthController::class, 'me']);
    Route::post('/api/logout', [AuthController::class, 'logout']);

    // Список пользователей для вкладки "Пользователи" wpadmin
    Route::get('/api/admin/users', [AdminController::class, 'getUsers']);
});