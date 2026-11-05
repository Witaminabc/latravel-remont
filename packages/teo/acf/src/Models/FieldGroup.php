<?php

namespace Teo\Acf\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Группа полей, созданная/изменённая через админку.
 *
 * Поля и локации хранятся в JSON: реестр работает с массивами,
 * поэтому нормализованная отдельная таблица полей здесь не нужна.
 */
class FieldGroup extends Model
{
    protected $table = 'acf_field_groups';

    protected $fillable = [
        'key',
        'title',
        'location',
        'fields',
        'active',
    ];

    protected $casts = [
        'location' => 'array',
        'fields' => 'array',
        'active' => 'boolean',
    ];
}