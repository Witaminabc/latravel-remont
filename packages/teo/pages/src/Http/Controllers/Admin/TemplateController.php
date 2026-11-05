<?php

namespace Teo\Pages\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Teo\Pages\Support\TemplateRegistry;

class TemplateController extends Controller
{
    public function __construct(protected TemplateRegistry $templates)
    {
    }

    /**
     * GET /api/admin/page-templates — список шаблонов для формы страницы.
     *
     * ?post_type=page|post — фильтр по типу контента.
     * В режиме отладки дополнительно логирует группы из groups[], отсутствующие
     * в реестре teo/acf (незарегистрированные блоки — ошибка конфигурации).
     */
    public function index(Request $request)
    {
        $type = (string) $request->query('post_type', 'page');

        $templates = collect($this->templates->forType($type));

        if (config('app.debug') && class_exists(\Teo\Acf\Support\FieldGroupRegistry::class)) {
            $acfGroups = app(\Teo\Acf\Support\FieldGroupRegistry::class);

            foreach ($templates as $template) {
                foreach ($template['groups'] as $groupKey) {
                    if (!$acfGroups->group($groupKey)) {
                        \Illuminate\Support\Facades\Log::warning(
                            'teo/pages: группа блоков шаблона не зарегистрирована в teo/acf',
                            ['template' => $template['key'], 'group' => $groupKey]
                        );
                    }
                }
            }
        }

        return response()->json(
            $templates
                ->map(static fn (array $template): array => [
                    'key' => $template['key'],
                    'title' => $template['title'],
                    'description' => $template['description'],
                    'component' => $template['component'],
                    'groups' => $template['groups'],
                    'default' => (bool) $template['default'],
                ])
                ->values()
        );
    }
}