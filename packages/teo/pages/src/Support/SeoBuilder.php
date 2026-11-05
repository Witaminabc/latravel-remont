<?php

namespace Teo\Pages\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Сборка SEO-данных публичной страницы.
 *
 * Логика перенесена из PublicWelcomeController (getSeoData/generateDescription/
 * getSiteSettings) с сохранением ключей кэша: page_{slug}, site_settings —
 * существующая админская очистка кэша продолжает работать.
 */
class SeoBuilder
{
    /**
     * SEO-данные для страницы.
     *
     * @param array{meta?: array<string, mixed>, content?: string} $override
     *        Переопределения для предпросмотра несохранённого состояния (Фаза 7).
     * @return array<string, mixed>
     */
    public function forPage(object $page, array $override = []): array
    {
        $meta = $override['meta'] ?? ($page->meta ?? []);
        $content = $override['content'] ?? ($page->content ?? '');

        $settings = $this->siteSettings();
        $siteName = $settings['name'] ?? config('app.name', 'Мой сайт');

        $description = $meta['description'] ?? $this->generateDescription($content);

        $featuredUrl = $this->absoluteUrl($page->featured_image ?? null);

        return [
            'title' => !empty($meta['title']) ? $meta['title'] : $page->title . ' | ' . $siteName,
            'description' => $description,
            'keywords' => $meta['keywords'] ?? '',
            'robots' => 'index, follow',
            'canonical' => $meta['canonical'] ?? url()->current(),

            'og_type' => 'website',
            'og_site_name' => $siteName,
            'og_locale' => 'ru_RU',
            'og_title' => $page->title,
            'og_description' => $meta['description'] ?? '',
            'og_url' => url()->current(),
            'og_image' => $featuredUrl,

            'twitter_card' => 'summary_large_image',
            'twitter_title' => $page->title,
            'twitter_description' => $meta['description'] ?? '',
            'twitter_image' => $featuredUrl,

            // Для корневого Blade (favicon из настроек сайта)
            'favicon' => $settings['favicon'] ?? null,

            // JSON-LD: WebSite + Organization (всегда), BreadcrumbList (вложенные страницы)
            'json_ld' => $this->jsonLd($page, $settings, $siteName),
        ];
    }

    /**
     * Структурированные данные страницы (массив схем JSON-LD).
     *
     * @param array<string, mixed> $settings
     * @return list<array<string, mixed>>
     */
    protected function jsonLd(object $page, array $settings, string $siteName): array
    {
        $items = [];

        $website = [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $siteName,
            'url' => url('/'),
        ];
        if (!empty($settings['description'])) {
            $website['description'] = $settings['description'];
        }
        $items[] = $website;

        $organization = [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => url('/'),
        ];
        if (!empty($settings['logo'])) {
            $organization['logo'] = $settings['logo'];
        }
        $sameAs = array_values(array_filter([
            $settings['telegram'] ?? '',
            $settings['max'] ?? '',
            $settings['vk'] ?? '',
            $settings['whatsapp'] ?? '',
            $settings['instagram'] ?? '',
            $settings['facebook'] ?? '',
            $settings['twitter'] ?? '',
            $settings['youtube'] ?? '',
            $settings['github'] ?? '',
        ]));
        if ($sameAs !== []) {
            $organization['sameAs'] = $sameAs;
        }
        $items[] = $organization;

        $breadcrumbs = $this->breadcrumbs($page);
        if ($breadcrumbs !== null) {
            $items[] = $breadcrumbs;
        }

        return $items;
    }

    /**
     * BreadcrumbList по цепочке parent_id (только для вложенных страниц).
     *
     * @return array<string, mixed>|null
     */
    protected function breadcrumbs(object $page): ?array
    {
        if (empty($page->parent_id)) {
            return null;
        }

        $model = config('pages.model');
        $ancestors = [];
        $parentId = $page->parent_id;
        $guard = 0;

        while (!empty($parentId) && $guard++ < 10) {
            $parent = Cache::remember(
                'page_breadcrumb_' . $parentId,
                (int) config('pages.cache_ttl', 300),
                fn () => $model::query()->find($parentId, ['id', 'title', 'slug', 'parent_id'])
            );

            if ($parent === null) {
                break;
            }

            array_unshift($ancestors, $parent);
            $parentId = $parent->parent_id;
        }

        if ($ancestors === []) {
            return null;
        }

        $list = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => 'Главная',
                'item' => $this->pageUrl(null),
            ],
        ];

        $position = 2;
        foreach ($ancestors as $ancestor) {
            $list[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $ancestor->title,
                'item' => $this->pageUrl($ancestor->slug),
            ];
        }

        $list[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $page->title,
            'item' => $this->pageUrl($page->slug ?? null),
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $list,
        ];
    }

    /**
     * Публичный URL страницы (slug 'home' и пустой slug → корень).
     */
    protected function pageUrl(?string $slug): string
    {
        return (!empty($slug) && $slug !== 'home') ? url('/' . $slug) : url('/');
    }

    /**
     * Абсолютный URL картинки: полный URL оставляем как есть, путь от корня
     * домена переводим через url(), относительный — через asset('storage/...').
     */
    protected function absoluteUrl(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        if (preg_match('#^(?:https?:)?//#i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/')) {
            return url($path);
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Генерация описания из контента (первые 160 символов текста).
     *
     * Контент предварительно проходит санитизацию: strip_tags один сохраняет
     * текстовое содержимое <script>/<style>, оно не должно попадать в description.
     */
    public function generateDescription(?string $content): string
    {
        if (!$content) {
            return '';
        }

        $text = strip_tags(HtmlSanitizer::clean($content) ?? '');
        $text = preg_replace('/\s+/', ' ', $text);

        return mb_substr($text, 0, 160);
    }

    /**
     * Настройки сайта (тот же ключ кэша, что и в легаси-контроллере).
     *
     * @return array<string, mixed>
     */
    public function siteSettings(): array
    {
        return Cache::remember(
            'site_settings',
            (int) config('pages.settings_cache_ttl', 3600),
            function () {
                $settingModel = config('pages.setting_model', \App\Models\Setting::class);
                $settings = $this->rawSettings((string) $settingModel);

                return [
                    'name' => $settings['site_name'] ?? 'Мой сайт',
                    'description' => $settings['site_description'] ?? '',
                    'keywords' => $settings['site_keywords'] ?? '',
                    'logo' => !empty($settings['site_logo']) ? asset('storage/' . $settings['site_logo']) : null,
                    'favicon' => !empty($settings['site_favicon']) ? asset('storage/' . $settings['site_favicon']) : null,
                    'phone' => $settings['contact_phone'] ?? '',
                    'email' => $settings['contact_email'] ?? '',
                    'address' => $settings['contact_address'] ?? '',
                    'hours' => $settings['contact_hours'] ?? '',
                    'map_embed' => $settings['contact_map_embed'] ?? '',
                    'map_lat' => $settings['contact_map_lat'] ?? '',
                    'map_lng' => $settings['contact_map_lng'] ?? '',
                    'map_zoom' => $settings['contact_map_zoom'] ?? 16,
                    'map_link' => $settings['contact_map_link'] ?? '',
                    'note' => $settings['contact_note'] ?? '',
                    'facebook' => $settings['social_facebook'] ?? '',
                    'twitter' => $settings['social_twitter'] ?? '',
                    'instagram' => $settings['social_instagram'] ?? '',
                    'telegram' => $settings['social_telegram'] ?? '',
                    'max' => $settings['social_max'] ?? '',
                    'vk' => $settings['social_vk'] ?? '',
                    'whatsapp' => $settings['social_whatsapp'] ?? '',
                    'youtube' => $settings['social_youtube'] ?? '',
                    'github' => $settings['social_github'] ?? '',

                    // Шапка публичного сайта: пункты меню и заполненные соцсети
                    'menu_items' => $this->menuItems($settings),
                    'socials' => $this->socialLinks($settings),

                    'google_analytics_id' => $settings['google_analytics_id'] ?? '',
                    'yandex_metrika_id' => $settings['yandex_metrika_id'] ?? '',
                ];
            }
        );
    }

    /**
     * Сырые значения настроек из БД.
     *
     * Читаем через Query Builder, а не через модель: исторические значения
     * могли быть записаны как обычные строки, и json-cast модели превращал
     * их в null. JSON-строки (в т.ч. от админки) декодируем здесь же.
     *
     * @param class-string $modelClass
     * @return array<string, mixed>
     */
    protected function rawSettings(string $modelClass): array
    {
        try {
            $rows = \Illuminate\Support\Facades\DB::table((new $modelClass)->getTable())
                ->pluck('value', 'key')
                ->all();
        } catch (\Throwable) {
            return [];
        }

        $settings = [];

        foreach ($rows as $key => $value) {
            $settings[(string) $key] = $this->decodeSettingValue($value);
        }

        return $settings;
    }

    /**
     * Строка из settings может быть JSON-значением ("текст", массив, объект)
     * или обычной строкой — возвращаем соответствующее PHP-значение.
     */
    protected function decodeSettingValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        $decoded = json_decode($trimmed, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Пункты меню (шапка и футер) с вложенностью.
     *
     * Приоритет: настройка menu_items, если все её внутренние ссылки ведут
     * на существующие опубликованные страницы; иначе — автоматическое меню
     * из реальной иерархии страниц (только существующие пункты).
     *
     * @param array<string, mixed> $settings
     * @return list<array{title: string, url: string, order: int, children: array}>
     */
    protected function menuItems(array $settings): array
    {
        $configured = $this->normalizeMenuItems(
            $this->decodeJsonArray($settings['menu_items'] ?? null)
        );

        if ($configured !== [] && $this->menuLinksExist($configured, $this->existingPagePaths())) {
            return $configured;
        }

        return $this->autoMenu();
    }

    /**
     * Нормализация пунктов меню (не глубже двух уровней) и их сортировка.
     *
     * @param array<int, mixed> $items
     * @return list<array{title: string, url: string, order: int, children: array}>
     */
    protected function normalizeMenuItems(array $items, int $depth = 0): array
    {
        usort($items, static function ($a, $b): int {
            return (int) (is_array($a) ? ($a['order'] ?? 0) : 0)
                <=> (int) (is_array($b) ? ($b['order'] ?? 0) : 0);
        });

        $clean = [];
        $order = 0;

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $url = $this->normalizeMenuUrl($item['url'] ?? '');

            if ($title === '' || $url === '') {
                continue;
            }

            $children = $depth < 1
                ? $this->normalizeMenuItems((array) ($item['children'] ?? []), $depth + 1)
                : [];

            $clean[] = [
                'title' => $title,
                'url' => $url,
                'order' => $order++,
                'children' => $children,
            ];
        }

        return $clean;
    }

    /**
     * Все ли внутренние ссылки меню ведут на существующие страницы.
     *
     * @param array<int, array> $items
     * @param array<string, bool> $paths
     */
    protected function menuLinksExist(array $items, array $paths): bool
    {
        foreach ($items as $item) {
            $url = (string) ($item['url'] ?? '');

            if (str_starts_with($url, '/') && !isset($paths[$url])) {
                return false;
            }

            if (!$this->menuLinksExist((array) ($item['children'] ?? []), $paths)) {
                return false;
            }
        }

        return true;
    }

    /**
     * URL опубликованных страниц (включая вложенные) → true.
     *
     * @return array<string, bool>
     */
    protected function existingPagePaths(): array
    {
        $model = config('pages.model');

        if (!is_string($model) || !class_exists($model)) {
            return [];
        }

        try {
            $pages = $model::query()
                ->where('type', 'page')
                ->where('status', 'published')
                ->get(['id', 'slug', 'parent_id']);
        } catch (\Throwable) {
            return [];
        }

        $slugs = [];
        foreach ($pages as $page) {
            $slugs[(int) $page->id] = trim((string) ($page->slug ?? ''));
        }

        $paths = [];

        foreach ($pages as $page) {
            $slug = $slugs[(int) $page->id] ?? '';

            if ($slug === '') {
                continue;
            }

            $url = $slug === 'home' ? '/' : '/' . $slug;

            $parentId = (int) ($page->parent_id ?? 0);
            if ($parentId > 0 && !empty($slugs[$parentId])) {
                $url = '/' . $slugs[$parentId] . '/' . $slug;
            }

            $paths[$url] = true;
        }

        return $paths;
    }

    /**
     * Меню из реальной иерархии опубликованных страниц (2 уровня).
     *
     * @return list<array{title: string, url: string, order: int, children: array}>
     */
    protected function autoMenu(): array
    {
        $model = config('pages.model');

        if (!is_string($model) || !class_exists($model)) {
            return [];
        }

        try {
            $pages = $model::query()
                ->where('type', 'page')
                ->where('status', 'published')
                ->orderBy('order')
                ->orderBy('id')
                ->get(['id', 'title', 'slug', 'parent_id', 'meta']);
        } catch (\Throwable) {
            return [];
        }

        // Демо-страницы не должны попадать в меню сайта
        $excludedTemplates = ['fields-test'];

        $slugs = [];
        $top = [];
        $children = [];

        foreach ($pages as $page) {
            $slugs[(int) $page->id] = trim((string) ($page->slug ?? ''));
        }

        foreach ($pages as $page) {
            $id = (int) $page->id;
            $slug = $slugs[$id] ?? '';

            if ($slug === '') {
                continue;
            }

            $meta = is_array($page->meta) ? $page->meta : [];
            if (in_array($meta['template'] ?? null, $excludedTemplates, true)) {
                continue;
            }

            $parentId = (int) ($page->parent_id ?? 0);

            if ($parentId > 0) {
                $children[$parentId][] = $id;
                continue;
            }

            $top[$id] = [
                'title' => $slug === 'home' ? 'Главная' : (string) $page->title,
                'url' => $slug === 'home' ? '/' : '/' . $slug,
                'order' => 0,
                'children' => [],
            ];
        }

        foreach ($children as $parentId => $ids) {
            if (!isset($top[$parentId])) {
                continue;
            }

            $parentSlug = $slugs[$parentId] ?? '';

            if ($parentSlug === '') {
                continue;
            }

            $order = 0;

            foreach ($ids as $id) {
                $childSlug = $slugs[$id] ?? '';
                $child = $pages->firstWhere('id', $id);

                if ($childSlug === '' || !$child) {
                    continue;
                }

                $top[$parentId]['children'][] = [
                    'title' => (string) $child->title,
                    'url' => '/' . $parentSlug . '/' . $childSlug,
                    'order' => $order++,
                    'children' => [],
                ];
            }
        }

        $items = array_values($top);

        // «Главная» всегда первой, остальные — в порядке страниц
        usort($items, static function (array $a, array $b): int {
            $aHome = $a['url'] === '/' ? 0 : 1;
            $bHome = $b['url'] === '/' ? 0 : 1;

            return [$aHome, $a['order']] <=> [$bHome, $b['order']];
        });

        foreach ($items as $index => $item) {
            $items[$index]['order'] = $index;
        }

        return $items;
    }

    /**
     * Абсолютный URL, якорь, mailto/tel — как есть; иначе корневой путь.
     */
    protected function normalizeMenuUrl(mixed $value): string
    {
        $url = trim((string) $value);

        if ($url === '') {
            return '';
        }

        if (preg_match('#^(https?://|mailto:|tel:)#i', $url) === 1 || str_starts_with($url, '#')) {
            return $url;
        }

        return '/' . ltrim($url, '/');
    }

    /**
     * Список заполненных соцсетей для шапки (порядок — как в SOCIAL_ORDER).
     *
     * @param array<string, mixed> $settings
     * @return list<array{key: string, label: string, url: string}>
     */
    protected function socialLinks(array $settings): array
    {
        $networks = [
            'telegram' => 'Telegram',
            'max' => 'MAX',
            'vk' => 'VK',
            'whatsapp' => 'WhatsApp',
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'twitter' => 'X (Twitter)',
            'youtube' => 'YouTube',
            'github' => 'GitHub',
        ];

        $links = [];

        foreach ($networks as $key => $label) {
            $url = trim((string) ($settings['social_' . $key] ?? ''));

            if ($url === '' || preg_match('#^https?://#i', $url) !== 1) {
                continue;
            }

            $links[] = [
                'key' => $key,
                'label' => $label,
                'url' => $url,
                'logo' => $this->settingAssetUrl($settings['social_' . $key . '_logo'] ?? null),
            ];
        }

        return $links;
    }

    /**
     * URL файла из настройки: абсолютный URL, /storage/... или путь в storage.
     */
    protected function settingAssetUrl(mixed $value): ?string
    {
        $path = trim((string) $value);

        if ($path === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return asset(ltrim($path, '/'));
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    /**
     * Декодирование JSON-настройки. Значение может быть массивом (json-cast
     * модели) или строкой, в том числе дважды закодированной старым сидером.
     *
     * @return array<int|string, mixed>
     */
    protected function decodeJsonArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return is_array($decoded) ? $decoded : [];
    }
}