<?php

namespace Teo\Acf\Fields;

class BooleanField extends FieldType
{
    public function name(): string
    {
        return 'boolean';
    }

    public function label(): string
    {
        return 'Переключатель';
    }

    public function default(): mixed
    {
        return false;
    }

    public function rules(array $field): array
    {
        return [($field['required'] ?? false) ? 'required' : 'nullable', 'boolean'];
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        return (bool) $value;
    }
}
