<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Teo\Pages\Http\Controllers\Admin\PreviewController;
use Teo\Pages\Http\Controllers\Admin\TemplateController;
use Teo\Pages\Http\Controllers\PageTemplateController;
use Teo\Pages\Http\Controllers\SitemapController;

/*
|--------------------------------------------------------------------------
| Публичный catch-all
|--------------------------------------------------------------------------
| Route::fallback() регистрируется фреймворком отдельно и матчится последним
| среди всех роутов приложения — не перехватывает /admin, /api/* и т.д.
| (в отличие от прямого Route::get('/{slug}') в routes/simple.php).
*/

Route::middleware('web')->group(function () {
    Route::fallback(function (Request $request) {
        if (!in_array($request->method(), ['GET', 'HEAD'], true) || $request->expectsJson()) {
            abort(404);
        }

        $path = trim($request->path(), '/');

        return app(PageTemplateController::class)->show($request, $path !== '' ? $path : null);
    });

    // Публичная ссылка на черновик: подпись URL (ValidateSignature), TTL —
    // config('pages.preview_link_ttl'). Рендерит запись любого статуса
    // с robots=noindex для непубликованных.
    Route::get('/preview/{id}', [PageTemplateController::class, 'previewByLink'])
        ->middleware('signed')
        ->name('pages.preview');

    // Карта сайта: опубликованные страницы/записи (Фаза 8)
    Route::get('/sitemap.xml', SitemapController::class)->name('pages.sitemap');
});

/*
|--------------------------------------------------------------------------
| Админский API
|--------------------------------------------------------------------------
*/

Route::prefix(config('pages.admin.prefix', 'api/admin'))
    ->group(function () {
        Route::middleware(config('pages.admin.middleware', ['auth:sanctum']))
            ->group(function () {
                Route::get('page-templates', [TemplateController::class, 'index']);
                Route::post('pages/preview', [PreviewController::class, 'store']);
                Route::post('pages/preview-link', [PreviewController::class, 'link']);
            });
    });