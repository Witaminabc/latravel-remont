<?php

namespace Teo\Acf\Fields;

class WysiwygField extends FieldType
{
    public function name(): string
    {
        return 'wysiwyg';
    }

    public function label(): string
    {
        return 'Визуальный редактор';
    }

    public function default(): mixed
    {
        return '';
    }

    public function rules(array $field): array
    {
        return [($field['required'] ?? false) ? 'required' : 'nullable', 'string'];
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        return $value === null ? null : (string) $value;
    }
}
