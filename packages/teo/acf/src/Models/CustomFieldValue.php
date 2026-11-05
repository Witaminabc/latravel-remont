<?php

namespace Teo\Acf\Models;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'field_name',
        'field_key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    public function getTable(): string
    {
        return config('acf.table', 'custom_field_values');
    }
}
