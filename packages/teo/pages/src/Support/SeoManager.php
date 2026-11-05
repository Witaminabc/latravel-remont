<?php

namespace Teo\Pages\Support;

/**
 * SEO-данные текущего запроса.
 *
 * Контроллер кладёт данные перед Inertia::render(), корневой Blade-шаблон
 * (resources/views/inertia.blade.php) читает их в <head> — мета попадает
 * в первый HTML-ответ до выполнения JavaScript.
 */
class SeoManager
{
    /** @var array<string, mixed> */
    protected array $seo = [];

    /** @param array<string, mixed> $seo */
    public function set(array $seo): void
    {
        $this->seo = $seo;
    }

    /** @return array<string, mixed> */
    public function get(): array
    {
        return $this->seo;
    }

    public function reset(): void
    {
        $this->seo = [];
    }

    /** @return array<string, mixed> */
    public static function current(): array
    {
        return app(static::class)->get();
    }

    /** @param array<string, mixed> $seo */
    public static function put(array $seo): void
    {
        app(static::class)->set($seo);
    }
}