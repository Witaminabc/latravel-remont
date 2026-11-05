<?php

namespace Teo\Acf\Fields;

class NumberField extends FieldType
{
    public function name(): string
    {
        return 'number';
    }

    public function label(): string
    {
        return 'Число';
    }

    public function default(): mixed
    {
        return null;
    }

    public function rules(array $field): array
    {
        return array_values(array_filter([
            ($field['required'] ?? false) ? 'required' : 'nullable',
            'numeric',
            isset($field['min']) ? 'min:' . $field['min'] : null,
            isset($field['max']) ? 'max:' . $field['max'] : null,
        ]));
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        if ($value === null || $value === '') {
            return null;
        }

        return str_contains((string) $value, '.') ? (float) $value : (int) $value;
    }
}
