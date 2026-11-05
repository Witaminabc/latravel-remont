<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Teo\Admin\Http\Controllers\Admin\{
    PageController,
    MediaController,
    TaxonomyController,
    DashboardController,
    SettingsController,
    UserController
};

// Публичные маршруты API (auth остаётся общей инфраструктурой в app/)
Route::post('/api/login', [AuthController::class, 'login']);
Route::post('/api/register', [AuthController::class, 'register']);

// Защищенные маршруты API
Route::prefix('api/admin')->middleware(['auth:sanctum'])->group(function () {

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    Route::get('/dashboard/activity', [DashboardController::class, 'activity']);

    // Pages и Posts используют один контроллер
    Route::apiResource('pages', PageController::class);
    Route::apiResource('posts', PageController::class);

    // Дополнительные маршруты для страниц
    Route::post('/pages/{id}/restore', [PageController::class, 'restore']);
    Route::delete('/pages/{id}/force', [PageController::class, 'forceDelete']);
    Route::post('/pages/{id}/duplicate', [PageController::class, 'duplicate']);
    Route::post('/pages/reorder', [PageController::class, 'reorder']);
    Route::get('/pages/{id}/versions', [PageController::class, 'versions']);
    Route::post('/pages/{id}/preview', [PageController::class, 'preview']);

    // Дополнительные маршруты для постов
    Route::post('/posts/{id}/restore', [PageController::class, 'restore']);
    Route::delete('/posts/{id}/force', [PageController::class, 'forceDelete']);
    Route::post('/posts/{id}/duplicate', [PageController::class, 'duplicate']);

    // Media
    Route::post('/media/upload', [MediaController::class, 'upload']);
    Route::delete('/media/{id}', [MediaController::class, 'destroy']);
    Route::get('/media', [MediaController::class, 'index']);
    Route::post('/media/ckeditor-upload', [MediaController::class, 'ckeditorUpload']);

    // Taxonomies
    Route::apiResource('taxonomies', TaxonomyController::class);
    Route::post('/taxonomies/reorder', [TaxonomyController::class, 'reorder']);

    // Settings
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);
    Route::post('/settings/logo', [SettingsController::class, 'uploadLogo']);
    Route::post('/settings/social-logo', [SettingsController::class, 'uploadSocialLogo']);
    Route::post('/settings/social-logo/remove', [SettingsController::class, 'removeSocialLogo']);
    Route::post('/settings/cache/clear', [SettingsController::class, 'clearCache']);

    // Users (создание/обновление/удаление; список отдаёт Api\AdminController@getUsers)
    Route::post('/users', [UserController::class, 'store']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);

    // Заявки (teo/leads)
    Route::get('/leads', [\Teo\Leads\Http\Controllers\LeadController::class, 'index']);
    Route::patch('/leads/{id}/status', [\Teo\Leads\Http\Controllers\LeadController::class, 'updateStatus']);
});

// Маршрут для Vue SPA (должен матчиться раньше публичного fallback teo/pages)
Route::get('/admin/{any?}', function () {
    return view('admin::app');
})->where('any', '^(?!api).*$');
