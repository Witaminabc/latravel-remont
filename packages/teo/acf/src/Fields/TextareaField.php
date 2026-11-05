<?php

namespace Teo\Acf\Fields;

class TextareaField extends FieldType
{
    public function name(): string
    {
        return 'textarea';
    }

    public function label(): string
    {
        return 'Текстовое поле';
    }

    public function default(): mixed
    {
        return '';
    }

    public function rules(array $field): array
    {
        return array_values(array_filter([
            ($field['required'] ?? false) ? 'required' : 'nullable',
            'string',
            isset($field['max']) ? 'max:' . $field['max'] : null,
        ]));
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        return $value === null ? null : trim((string) $value);
    }
}
