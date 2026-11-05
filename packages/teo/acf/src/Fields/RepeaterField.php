<?php

namespace Teo\Acf\Fields;

use Teo\Acf\Support\FieldTypeRegistry;

class RepeaterField extends FieldType
{
    public function __construct(protected FieldTypeRegistry $types)
    {
    }

    public function name(): string
    {
        return 'repeater';
    }

    public function label(): string
    {
        return 'Репитер';
    }

    public function default(): mixed
    {
        return [];
    }

    public function rules(array $field): array
    {
        return array_values(array_filter([
            ($field['required'] ?? false) ? 'required' : 'nullable',
            'array',
            isset($field['min']) ? 'min:' . $field['min'] : null,
            isset($field['max']) ? 'max:' . $field['max'] : null,
        ]));
    }

    /**
     * Вложенные правила: values.{name}.*.{sub_field}
     */
    public function nestedRules(array $field, string $prefix): array
    {
        $rules = [];

        foreach ($field['sub_fields'] ?? [] as $sub) {
            $name = $sub['name'] ?? null;
            if (!$name) {
                continue;
            }

            $type = $this->types->make($sub['type'] ?? 'text');
            $subRules = array_values(array_filter(
                $type->rules($sub),
                fn ($rule) => $rule !== 'required'
            ));

            $rules[$prefix . '.*.' . $name] = array_merge(['nullable'], $subRules);
        }

        return $rules;
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        if (!is_array($value)) {
            return [];
        }

        $subFields = $field['sub_fields'] ?? [];
        $rows = [];

        foreach ($value as $row) {
            if (!is_array($row)) {
                continue;
            }

            $clean = [];
            foreach ($subFields as $sub) {
                $name = $sub['name'] ?? null;
                if (!$name) {
                    continue;
                }
                $type = $this->types->make($sub['type'] ?? 'text');
                $clean[$name] = $type->sanitize($row[$name] ?? null, $sub);
            }
            $rows[] = $clean;
        }

        return array_values($rows);
    }
}
