<?php

namespace Teo\Pages\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    /**
     * GET /sitemap.xml — опубликованные страницы и записи (loc + lastmod).
     *
     * Главная (/) всегда первым URL; результат кэшируется
     * (ключ sitemap.xml, TTL — config('pages.sitemap_cache_ttl')).
     */
    public function __invoke(): Response
    {
        $xml = Cache::remember(
            'sitemap.xml',
            (int) config('pages.sitemap_cache_ttl', 3600),
            fn (): string => $this->build()
        );

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }

    protected function build(): string
    {
        $model = config('pages.model');
        $postTypes = config('pages.post_types', ['page', 'post']);

        $rows = $model::query()
            ->whereIn('type', $postTypes)
            ->where('status', 'published')
            ->orderBy('slug')
            ->get(['slug', 'updated_at']);

        $items = [];

        $home = $rows->firstWhere('slug', 'home');
        if ($home !== null) {
            $items[] = ['loc' => url('/'), 'lastmod' => $home->updated_at];
        }

        foreach ($rows as $row) {
            if (empty($row->slug) || $row->slug === 'home') {
                continue;
            }

            $items[] = ['loc' => url('/' . $row->slug), 'lastmod' => $row->updated_at];
        }

        $body = '';
        foreach ($items as $item) {
            $body .= '  <url>' . "\n"
                . '    <loc>' . htmlspecialchars($item['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</loc>' . "\n"
                . ($item['lastmod'] !== null
                    ? '    <lastmod>' . $item['lastmod']->toIso8601String() . '</lastmod>' . "\n"
                    : '')
                . '  </url>' . "\n";
        }

        return '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n"
            . $body
            . '</urlset>' . "\n";
    }
}
