<?php

namespace Teo\Acf\Fields;

class SelectField extends FieldType
{
    public function name(): string
    {
        return 'select';
    }

    public function label(): string
    {
        return 'Выпадающий список';
    }

    public function default(): mixed
    {
        return null;
    }

    public function rules(array $field): array
    {
        $multiple = (bool) ($field['multiple'] ?? false);
        $values = array_map(
            fn ($o) => is_array($o) ? ($o['value'] ?? null) : $o,
            $field['options'] ?? []
        );
        $in = 'in:' . implode(',', array_filter($values, fn ($v) => $v !== null));

        $rules = [($field['required'] ?? false) ? 'required' : 'nullable'];

        if ($multiple) {
            return array_merge($rules, ['array']);
        }

        return array_merge($rules, ['string', $in]);
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        if (($field['multiple'] ?? false)) {
            return is_array($value) ? array_values($value) : ($value === null ? [] : [$value]);
        }

        return $value;
    }
}
