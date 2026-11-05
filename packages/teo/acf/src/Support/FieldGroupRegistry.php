<?php

namespace Teo\Acf\Support;

class FieldGroupRegistry
{
    /** @var array<string, array> */
    protected array $groups = [];

    public function addGroup(array $group): void
    {
        $key = $group['key'] ?? 'group_' . (count($this->groups) + 1);
        $group['key'] = $key;
        $group['fields'] = $group['fields'] ?? [];
        $group['location'] = $group['location'] ?? [];
        // Источник: 'code' — Acf::addGroup(), 'database' — строка из acf_field_groups.
        $group['source'] = $group['source'] ?? 'code';

        $this->groups[$key] = $group;
    }

    public function removeGroup(string $key): void
    {
        unset($this->groups[$key]);
    }

    /**
     * Полная замена содержимого реестра (используется репозиторием при гидрации).
     *
     * @param array<string, array> $groups Группы в формате key => group.
     */
    public function reset(array $groups): void
    {
        $this->groups = $groups;
    }

    /** @return array<int, array> */
    public function groups(): array
    {
        return array_values($this->groups);
    }

    public function group(string $key): ?array
    {
        return $this->groups[$key] ?? null;
    }

    /**
     * Группы, подходящие под локацию (тип материала + slug + шаблон страницы).
     * Правило `template` учитывается только когда передан $template —
     * обратная совместимость со старыми вызовами сохранена.
     *
     * @return array<int, array>
     */
    public function forLocation(?string $postType, ?string $slug = null, ?string $template = null): array
    {
        return array_values(array_filter(
            $this->groups(),
            function (array $group) use ($postType, $slug, $template) {
                foreach ($group['location'] as $rule) {
                    $type = $rule['type'] ?? null;
                    $value = $rule['value'] ?? null;

                    if ($type === 'post_type' && $postType !== null && $value !== $postType) {
                        return false;
                    }

                    if ($type === 'slug' && $slug !== null && $value !== $slug) {
                        return false;
                    }

                    if ($type === 'template' && $template !== null && $value !== $template) {
                        return false;
                    }
                }

                return true;
            }
        ));
    }

    /**
     * Все поля групп для локации в формате name => field.
     *
     * @return array<string, array>
     */
    public function fieldsFor(?string $postType, ?string $slug = null, ?string $template = null): array
    {
        $fields = [];

        foreach ($this->forLocation($postType, $slug, $template) as $group) {
            foreach ($group['fields'] as $field) {
                if (!empty($field['name'])) {
                    $fields[$field['name']] = $field;
                }
            }
        }

        return $fields;
    }
}
