<?php

namespace Teo\Pages\Support;

/**
 * Серверная санитизация HTML-контента публичных страниц.
 *
 * - allowlist тегов — всё за пределами списка снимается (strip_tags);
 * - блоки <script>/<style>/... удаляются вместе с содержимым;
 * - inline-обработчики (on*) и javascript: в href/src вырезаются;
 * - ровно один <h1>: первый сохраняется, последующие понижаются до <h2>
 *   (заголовок страницы рендерит шаблон — контент не должен давать второй h1).
 */
class HtmlSanitizer
{
    /**
     * Разрешённые теги. Запрещённые (script, style, iframe, ...) strip_tags
     * вырезает вместе с содержимым — после предварительной чистки блоков.
     */
    public const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><s><mark><ul><ol><li>'
        . '<h1><h2><h3><h4><h5><h6><blockquote><pre><code><a><img>'
        . '<figure><figcaption><table><thead><tbody><tr><th><td><div><span><hr><del><ins><sub><sup>';

    /** Теги, удаляемые вместе с содержимым (до strip_tags). */
    private const BLOCKED_WITH_CONTENT = ['script', 'style', 'iframe', 'object', 'embed', 'form', 'noscript', 'template', 'svg'];

    /** Теги, удаляемые как одиночные. */
    private const BLOCKED_EMPTY = ['link', 'meta', 'base', 'input', 'button', 'select', 'textarea', 'applet', 'frame', 'frameset'];

    public static function clean(?string $html): ?string
    {
        if ($html === null || trim($html) === '') {
            return $html;
        }

        $clean = $html;

        // 1. Опасные блоки — вместе с содержимым (<script>...</script> и т.п.)
        $withContent = implode('|', self::BLOCKED_WITH_CONTENT);
        $clean = preg_replace(
            '#<\s*(?:' . $withContent . ')\b[^>]*>.*?<\s*/\s*(?:' . $withContent . ')\s*>#is',
            '',
            $clean
        ) ?? $clean;

        // Одиночные опасные/служебные теги
        $empty = implode('|', self::BLOCKED_EMPTY);
        $clean = preg_replace('#<\s*(?:' . $empty . ')\b[^>]*/?>#is', '', $clean) ?? $clean;

        // 2. Allowlist: всё, что не разрешено, снимается (содержимое неразрешённых
        //    тегов, кроме script/style, strip_tags сохраняет как текст)
        $clean = strip_tags($clean, self::ALLOWED_TAGS);

        // 3. Инлайн-обработчики: on*="..."
        $clean = preg_replace('/\son[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $clean) ?? $clean;

        // 4. javascript: в href/src
        $clean = preg_replace(
            '/\s(?:href|src)\s*=\s*(?:"\s*javascript:[^"]*"|\'\s*javascript:[^\']*\'|\s*javascript:[^\s>]*)/i',
            '',
            $clean
        ) ?? $clean;

        // 5. Ровно один <h1> в контенте: первый остаётся, остальные → <h2>
        $firstH1 = true;
        $clean = preg_replace_callback(
            '/<h1\b[^>]*>(.*?)<\/h1>/is',
            static function (array $match) use (&$firstH1): string {
                if ($firstH1) {
                    $firstH1 = false;

                    return $match[0];
                }

                return '<h2>' . $match[1] . '</h2>';
            },
            $clean
        ) ?? $clean;

        return $clean;
    }
}
