<?php

namespace Teo\Pages\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\URL;
use Teo\Pages\Http\Controllers\PageTemplateController;

class PreviewController extends Controller
{
    public function __construct(protected PageTemplateController $pages)
    {
    }

    /**
     * POST /api/admin/pages/preview — HTML предпросмотра из снапшота формы.
     *
     * Принимает несохранённое состояние (title, content, meta, acf, template),
     * собирает виртуальную запись и рендерит её через общий renderPage():
     * тот же шаблон, та же SEO-мета, что и у публичной страницы.
     * Роботы получают noindex.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'type' => ['nullable', 'string', 'in:page,post'],
            'parent_id' => ['nullable', 'integer'],
            'featured_image' => ['nullable', 'string'],
            'published_at' => ['nullable', 'date'],
            'meta' => ['nullable', 'array'],
            'acf' => ['nullable', 'array'],
        ]);

        $model = config('pages.model');

        /** @var \Illuminate\Database\Eloquent\Model $page */
        $page = new $model();
        $page->forceFill([
            'title' => ($data['title'] ?? '') !== '' ? $data['title'] : 'Без названия',
            'slug' => $data['slug'] ?? null,
            'content' => $data['content'] ?? '',
            'excerpt' => $data['excerpt'] ?? null,
            'type' => $data['type'] ?? 'page',
            'status' => 'draft',
            'parent_id' => $data['parent_id'] ?? null,
            'featured_image' => $data['featured_image'] ?? null,
            'published_at' => $data['published_at'] ?? now(),
            'meta' => $data['meta'] ?? [],
        ]);

        $response = $this->pages->renderPage($page, [
            'meta' => $data['meta'] ?? [],
            'content' => $data['content'] ?? '',
            'acf' => $data['acf'] ?? [],
            'template' => data_get($data, 'meta.template'),
        ], [
            // Предпросмотр не должен индексироваться поисковиками
            'robots' => 'noindex, nofollow',
        ]);

        return $response;
    }

    /**
     * POST /api/admin/pages/preview-link — временная подписанная ссылка
     * на запись любого статуса (для отправки черновика без публикации).
     */
    public function link(Request $request)
    {
        $data = $request->validate(['id' => ['required', 'integer']]);

        $model = config('pages.model');
        $page = $model::query()->findOrFail($data['id']);

        $ttl = (int) config('pages.preview_link_ttl', 10080); // минуты (по умолчанию 7 дней)
        $expiresAt = now()->addMinutes($ttl);

        $url = URL::temporarySignedRoute('pages.preview', $expiresAt, ['id' => $page->id]);

        return response()->json([
            'url' => $url,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }
}