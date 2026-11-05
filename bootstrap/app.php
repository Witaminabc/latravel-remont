<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use App\Http\Middleware\HandleInertiaRequests;

use App\Providers\ChatServiceProvider;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',  // ← ДОБАВИТЬ API маршруты
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function ($router) {
        require base_path('/routes/simple.php');
        require base_path('/routes/chat.php');

    }
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Проект без маршрута login: без этого неавторизованный запрос
        // падал с Route [login] not defined (500) вместо JSON-401.
        $middleware->redirectGuestsTo(fn () => url('/'));

        // Добавляем Sanctum middleware в группу 'api'
        $middleware->group('api', [
            EnsureFrontendRequestsAreStateful::class,  // ← ВАЖНО для SPA режима
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        // Добавляем web middleware группу (если нужно)
        $middleware->group('web', [
            \Illuminate\Cookie\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // Inertia: shared-данные (appName, flash, auth) + корневой шаблон
            HandleInertiaRequests::class,
        ]);

        // Алиасы для middleware пакета teo/admin регистрируются
        // в AdminServiceProvider::boot() ('admin').

        // Глобальные middleware (выполняются при каждом запросе)
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class); // ← CORS для SPA
    })
    ->withProviders([
        App\Providers\ChatServiceProvider::class,
        Teo\Leads\LeadsServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        // Обработка исключений для API
        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Unauthenticated. Please login first.'
                ], 401);
            }
        });
    })->create();
