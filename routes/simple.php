<?php

use Illuminate\Support\Facades\Route;
use Teo\Pages\Http\Controllers\PageTemplateController;

// Публичные страницы рендерит пакет teo/pages (Inertia + серверная SEO-мета).
//
// Маршрут /{slug} удалён: вместо него используется Route::fallback внутри
// пакета — он матчится последним среди всех роутов приложения и поэтому
// не перехватывает /admin, /api/* и другие односегментные маршруты.
Route::middleware('web')->group(function () {
    // Легаси-алиас: до внедрения пакета рендерил главную (slug не передавался)
    Route::get('/standart', [PageTemplateController::class, 'show']);
    Route::get('/', [PageTemplateController::class, 'show']);
});
