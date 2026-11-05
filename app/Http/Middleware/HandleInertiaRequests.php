<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * Корневой шаблон для Инэртиа-страниц (СЕО-мета рендерирься на сервере).
     *
     * @var string
     */
    protected $rootView = 'inertia';

    /**
     * Версия ассетов для ассет версионинга.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Пропсы, общие для всех Инэртиа-страниц.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'appName' => config('app.name', 'Мой сайт'),
            'settings' => Setting::pluck('value', 'key')->all(),
        ];
    }
}
