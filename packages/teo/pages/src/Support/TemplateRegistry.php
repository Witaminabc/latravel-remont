<?php

namespace Teo\Pages\Support;

use Illuminate\Support\Str;

/**
 * Реестр шаблонов публичных страниц.
 *
 * Шаблоны регистрируются в коде проекта (см. App\Providers\PageTemplatesProvider):
 * сам шаблон — это Vue-компонент (resources/js/templates/*.vue) со статическим
 * составом секций; данные берутся из полей страницы (ACF + content).
 */
class TemplateRegistry
{
    /** @var array<string, array> */
    protected array $templates = [];

    /**
     * Добавить определение шаблона.
     *
     * Ожидаемые ключи:
     *  - key          (string, обязательный) машинный ключ, хранится в posts.meta.template
     *  - title        (string) название для админки
     *  - component    (string) имя Inertia-компонента, например "templates/Home"
     *  - types        (array)  типы контента (page/post/...)
     *  - description  (string) подсказка в админке
     *  - groups       (array)  ключи ACF-групп блоков этого шаблона (для панели блоков)
     *  - default      (bool)   шаблон по умолчанию для своих типов
     *  - json_ld      (string|array|null) схема JSON-LD (Фаза 8)
     */
    public function add(array $definition): self
    {
        $key = $definition['key'] ?? null;

        if (!is_string($key) || $key === '') {
            throw new \InvalidArgumentException('Page template definition requires a non-empty "key".');
        }

        $this->templates[$key] = array_merge([
            'key' => $key,
            'title' => $key,
            'component' => null,
            'types' => ['page'],
            'description' => '',
            'groups' => [],
            'default' => false,
            'json_ld' => null,
        ], $definition, ['key' => $key]);

        return $this;
    }

    /** @return array<int, array> */
    public function all(): array
    {
        return array_values($this->templates);
    }

    public function get(string $key): ?array
    {
        return $this->templates[$key] ?? null;
    }

    /** @return array<int, array> */
    public function forType(string $type): array
    {
        return array_values(array_filter(
            $this->templates,
            static fn (array $t): bool => in_array($type, (array) $t['types'], true)
        ));
    }

    /**
     * Резолв шаблона для записи.
     *
     * Приоритет: явный ключ (предпросмотр) → posts.meta.template → шаблон
     * с default=true для типа → config('pages.default_template') → первый для типа → null.
     */
    public function resolve(object $post, ?string $explicitKey = null): ?array
    {
        $type = (string) ($post->type ?? 'page');
        $suitable = fn (array $t): bool => in_array($type, (array) $t['types'], true);

        if ($explicitKey !== null && ($template = $this->get($explicitKey)) !== null && $suitable($template)) {
            return $template;
        }

        $metaKey = data_get($post->meta ?? null, 'template');
        if (is_string($metaKey) && $metaKey !== '' && ($template = $this->get($metaKey)) !== null && $suitable($template)) {
            return $template;
        }

        foreach ($this->forType($type) as $template) {
            if (!empty($template['default'])) {
                return $template;
            }
        }

        $globalKey = config('pages.default_template');
        if (is_string($globalKey) && $globalKey !== '' && ($template = $this->get($globalKey)) !== null) {
            return $template;
        }

        $forType = $this->forType($type);

        return $forType[0] ?? null;
    }
}