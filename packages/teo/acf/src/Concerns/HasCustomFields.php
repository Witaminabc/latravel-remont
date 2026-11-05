<?php

namespace Teo\Acf\Concerns;

use Teo\Acf\Models\CustomFieldValue;

trait HasCustomFields
{
    public function customFields()
    {
        return $this->morphMany(CustomFieldValue::class, 'model');
    }

    /**
     * Получить значение кастомного поля по машинному имени.
     */
    public function acf(string $name, mixed $default = null): mixed
    {
        if ($this->relationLoaded('customFields')) {
            $row = $this->customFields->firstWhere('field_name', $name);
        } else {
            $row = $this->customFields()->where('field_name', $name)->first();
        }

        return $row?->value ?? $default;
    }

    /**
     * Все значения полей в формате name => value.
     */
    public function acfValues(): array
    {
        $rows = $this->relationLoaded('customFields')
            ? $this->customFields
            : $this->customFields()->get();

        $values = [];
        foreach ($rows as $row) {
            $values[$row->field_name] = $row->value;
        }

        return $values;
    }

    /**
     * Сохранить значения полей (upsert по имени поля).
     */
    public function setAcf(array $values): void
    {
        foreach ($values as $name => $value) {
            $this->customFields()->updateOrCreate(
                ['field_name' => $name],
                ['value' => $value]
            );
        }

        $this->unsetRelation('customFields');
    }
}
