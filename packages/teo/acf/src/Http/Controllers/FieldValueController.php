<?php

namespace Teo\Acf\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Teo\Acf\Support\FieldGroupRegistry;
use Teo\Acf\Support\FieldTypeRegistry;

class FieldValueController extends Controller
{
    public function __construct(
        protected FieldGroupRegistry $groups,
        protected FieldTypeRegistry $types,
    ) {
    }

    public function show(string $type, $id): JsonResponse
    {
        $model = $this->resolve($type, $id);

        return response()->json(['data' => $model->acfValues()]);
    }

    public function update(Request $request, string $type, $id): JsonResponse
    {
        $model = $this->resolve($type, $id);

        $fields = $this->groups->fieldsFor($type, $model->slug ?? null, data_get($model->meta, 'template'));

        // Сбор правил валидации
        $rules = [];
        foreach ($fields as $name => $field) {
            $fieldType = $this->types->make($field['type'] ?? 'text');
            $rules["values.{$name}"] = $fieldType->rules($field);

            if (($field['type'] ?? null) === 'repeater') {
                $rules += $fieldType->nestedRules($field, "values.{$name}");
            }
        }

        if (!empty($rules)) {
            $request->validate($rules);
        }

        $input = $request->input('values', []);

        foreach ($fields as $name => $field) {
            $fieldType = $this->types->make($field['type'] ?? 'text');
            $value = $fieldType->sanitize(
                Arr::get($input, $name, $fieldType->default()),
                $field
            );

            $model->customFields()->updateOrCreate(
                ['field_name' => $name],
                ['field_key' => $field['key'] ?? null, 'value' => $value]
            );
        }

        // Сброс кеша публичной страницы: PageTemplateController кеширует
        // страницу как page_{slug} на pages.cache_ttl, иначе правки ACF
        // не видны на фронте до истечения TTL.
        if (!empty($model->slug)) {
            Cache::forget('page_' . $model->slug);
        }

        return response()->json(['data' => $model->fresh()->acfValues()]);
    }

    protected function resolve(string $type, $id): Model
    {
        $map = config('acf.models', []);
        $class = $map[$type] ?? null;

        abort_if(!$class || !class_exists($class), 404, "Unknown content type [{$type}]");

        return $class::findOrFail($id);
    }
}
