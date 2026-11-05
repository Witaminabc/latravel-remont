<?php

namespace Teo\Acf\Fields;

class ImageField extends FieldType
{
    public function name(): string
    {
        return 'image';
    }

    public function label(): string
    {
        return 'Изображение';
    }

    public function default(): mixed
    {
        return null;
    }

    public function rules(array $field): array
    {
        return [($field['required'] ?? false) ? 'required' : 'nullable', 'string'];
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        return $value === null || $value === '' ? null : (string) $value;
    }
}
