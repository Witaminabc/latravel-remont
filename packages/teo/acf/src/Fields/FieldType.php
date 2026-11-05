<?php

namespace Teo\Acf\Fields;

use Illuminate\Support\Str;

/**
 * Базовый тип поля. Каждый тип умеет:
 *  - отдавать значение по умолчанию,
 *  - возвращать правила валидации Laravel,
 *  - санитизировать значение перед сохранением,
 *  - приводить значение при выдаче,
 *  - отдавать дескриптор для фронтенда,
 *  - отдавать человекочитаемое название (для админки).
 */
abstract class FieldType
{
    /** Машинное имя типа (text, textarea, ...). */
    abstract public function name(): string;

    /** Название типа для интерфейса. */
    public function label(): string
    {
        return Str::headline($this->name());
    }

    public function default(): mixed
    {
        return null;
    }

    /**
     * @return array<int, mixed> Правила валидации для одного значения.
     */
    public function rules(array $field): array
    {
        return [($field['required'] ?? false) ? 'required' : 'nullable'];
    }

    public function sanitize(mixed $value, array $field): mixed
    {
        return $value;
    }

    public function cast(mixed $value, array $field): mixed
    {
        return $value;
    }

    /**
     * Дескриптор поля для фронтенда.
     */
    public function schema(array $field): array
    {
        return array_merge($field, ['type' => $this->name()]);
    }
}
