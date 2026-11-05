<?php

namespace Teo\Pages\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Teo\Pages\Support\HtmlSanitizer;
use Teo\Pages\Support\SeoBuilder;
use Teo\Pages\Support\SeoManager;
use Teo\Pages\Support\TemplateRegistry;

class PageTemplateController extends Controller
{
    public function __construct(
        protected TemplateRegistry $templates,
        protected SeoBuilder $seo,
    ) {
    }

    /**
     * Публичный рендер страницы по slug (fallback-роут пакета).
     * Поддерживает вложенность: /portfolio/remont-siberowo найдёт страницу
     * с slug=remont-siberowo, у которой parent_id = id страницы portfolio.
     */
    public function show(Request $request, ?string $slug = null): InertiaResponse
    {
        $currentSlug = $slug ?: 'home';
        $cacheKey = 'page_' . str_replace('/', ':', $currentSlug);

        $postTypes = config('pages.post_types', ['page', 'post']);

        $page = Cache::remember($cacheKey, (int) config('pages.cache_ttl', 300), function () use ($currentSlug, $postTypes) {
            $model = config('pages.model');

            // Вложенный путь (например portfolio/remont-siberowo)
            if (str_contains($currentSlug, '/')) {
                $segments = explode('/', $currentSlug);
                $parentSlug = $segments[0];
                $childSlug = $segments[1];

                $parent = $model::query()
                    ->whereIn('type', $postTypes)
                    ->where('slug', $parentSlug)
                    ->where('status', 'published')
                    ->first();

                if (!$parent) {
                    return null;
                }

                return $model::query()
                    ->whereIn('type', $postTypes)
                    ->where('slug', $childSlug)
                    ->where('parent_id', $parent->id)
                    ->where('status', 'published')
                    ->first();
            }

            return $model::query()
                ->whereIn('type', $postTypes)
                ->where('slug', $currentSlug)
                ->whereNull('parent_id')
                ->where('status', 'published')
                ->first();
        });

        if (!$page) {
            abort(404);
        }

        return $this->renderPage($page);
    }

    /**
     * GET /preview/{id} — публичный предпросмотр записи по подписанной ссылке
     * (middleware 'signed' проверяет подпись; TTL — config('pages.preview_link_ttl')).
     * Работает для любого статуса; непубликованные записи отдаются с noindex.
     */
    public function previewByLink(Request $request, $id): InertiaResponse
    {
        $model = config('pages.model');

        /** @var \Illuminate\Database\Eloquent\Model $page */
        $page = $model::query()->findOrFail($id);

        $seoOverrides = ($page->status ?? null) !== 'published'
            ? ['robots' => 'noindex, nofollow']
            : [];

        return $this->renderPage($page, [], $seoOverrides);
    }

    /**
     * Сборка и отдача Inertia-страницы (общий путь для показа и предпросмотра).
     *
     * @param array{meta?: array<string, mixed>, content?: string, acf?: array<string, mixed>, template?: string|null} $override
     *        Переопределения несохранённого состояния формы — используются предпросмотром (Фаза 7).
     * @param array<string, mixed> $seoOverrides
     *        Правки SEO-массива (например robots=noindex для предпросмотра) — применяются
     *        и в meta-тегах (SeoManager), и в props.seo (Inertia) — без рассинхрона.
     */
    public function renderPage(object $page, array $override = [], array $seoOverrides = []): InertiaResponse
    {
        $settings = $this->seo->siteSettings();
        $seo = $this->seo->forPage($page, $override);

        if ($seoOverrides !== []) {
            $seo = array_merge($seo, $seoOverrides);
        }

        $template = $this->templates->resolve($page, $override['template'] ?? null);

        if ($template === null) {
            Log::warning('teo/pages: шаблон не разрешён для страницы', [
                'slug' => $page->slug ?? null,
                'type' => $page->type ?? null,
                'registered_templates' => array_column($this->templates->all(), 'key'),
            ]);
        }

        // JSON-LD из шаблона (опциональный ключ) — добавляется к глобальным схемам
        if (!empty($template['json_ld'])) {
            $extra = $template['json_ld'];
            if (is_callable($extra)) {
                $extra = $extra($page, $seo);
            }
            if (is_array($extra)) {
                $seo['json_ld'] = array_merge($seo['json_ld'] ?? [], array_values($extra));
            }
        }

        SeoManager::put($seo);

        $component = $template['component'] ?? config('pages.default_component', 'templates/Default');

        return Inertia::render($component, [
            'template' => $template['key'] ?? null,
            'page' => $this->pagePayload($page, $override),
            'acf' => $override['acf'] ?? $this->acfValues($page),
            'seo' => $seo,
            'settings' => $settings,
        ]);
    }

    /**
     * Данные страницы для пропсов (имена полей совпадают с легаси PageView.vue).
     *
     * @param array{meta?: array<string, mixed>, content?: string} $override
     * @return array<string, mixed>
     */
    protected function pagePayload(object $page, array $override = []): array
    {
        $author = null;
        if (!empty($page->author_id)) {
            $author = $page->author()->first(['id', 'name']);
        }

        return [
            'id' => $page->id,
            'title' => $page->title,
            'slug' => $page->slug,
            'content' => HtmlSanitizer::clean($override['content'] ?? $page->content),
            'excerpt' => $page->excerpt,
            'type' => $page->type,
            'status' => $page->status,
            'featured_image' => $page->featured_image,
            'featured_image_url' => !empty($page->featured_image) ? asset('storage/' . $page->featured_image) : null,
            'meta' => $override['meta'] ?? ($page->meta ?? []),
            'parent_id' => $page->parent_id,
            'published_at' => $page->published_at?->toDateTimeString(),
            'view_url' => $page->view_url,
            'author' => $author,
        ];
    }

    /**
     * Значения кастомных полей (teo/acf): name => value.
     *
     * @return array<string, mixed>
     */
    protected function acfValues(object $page): array
    {
        return method_exists($page, 'acfValues') ? $page->acfValues() : [];
    }
}