<?php

namespace Teo\Acf\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Teo\Acf\Support\FieldGroupRegistry;
use Teo\Acf\Support\FieldGroupRepository;
use Teo\Acf\Support\FieldTypeRegistry;

class FieldGroupController extends Controller
{
    public function __construct(
        protected FieldGroupRegistry $groups,
        protected FieldTypeRegistry $types,
        protected FieldGroupRepository $repository,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $postType = $request->query('post_type');
        $slug = $request->query('slug');
        $template = $request->query('template');

        $groups = array_map(
            fn (array $group) => $this->serializeGroup($group),
            $this->groups->forLocation($postType, $slug, $template)
        );

        return response()->json(['data' => $groups]);
    }

    public function show(string $key): JsonResponse
    {
        $group = $this->groups->group($key);

        abort_if(!$group, 404, "Field group [{$key}] not found");

        return response()->json(['data' => $this->serializeGroup($group)]);
    }

    /**
     * Список доступных типов полей (для билдера полей в админке).
     */
    public function types(): JsonResponse
    {
        $data = array_map(
            fn (string $name) => [
                'value' => $name,
                'label' => $this->types->make($name)->label(),
                'has_sub_fields' => $name === 'repeater',
            ],
            $this->types->names()
        );

        return response()->json(['data' => $data]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validateGroup($request);

        if ($this->groups->group($data['key'])) {
            throw ValidationException::withMessages([
                'key' => "Группа с ключем [{$data['key']}] уже существует.",
            ]);
        }

        $this->repository->create($data);
        $this->repository->hydrate($this->groups);

        $group = $this->groups->group($data['key']);

        abort_if(!$group, 500, 'Не удалось создать группу полей');

        return response()->json(['data' => $this->serializeGroup($group)], 201);
    }

    public function update(Request $request, string $key): JsonResponse
    {
        abort_if(!$this->groups->group($key), 404, "Field group [{$key}] not found");

        $data = $this->validateGroup($request, $key);

        $this->repository->update($key, $data);
        $this->repository->hydrate($this->groups);

        return response()->json(['data' => $this->serializeGroup($this->groups->group($key))]);
    }

    public function destroy(string $key): JsonResponse
    {
        abort_if(!$this->groups->group($key), 404, "Field group [{$key}] not found");

        $this->repository->delete($key);
        $this->repository->hydrate($this->groups);

        return response()->json(['data' => ['key' => $key, 'deleted' => true]]);
    }

    /**
     * Валидация и нормализация группы. Ключ при редактировании неизменяем
     * (на нём могут ссылаться существующие данные/код).
     *
     * @return array{key: string, title: string, location: array, fields: array}
     */
    protected function validateGroup(Request $request, ?string $ignoreKey = null): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'key' => ['nullable', 'string', 'max:190', 'regex:/^[a-z][a-z0-9_]*$/'],
            'location' => ['required', 'array', 'min:1'],
            'location.*.type' => ['required', 'string', 'in:post_type,slug,template'],
            'location.*.value' => ['nullable', 'string', 'max:190'],
            'fields' => ['nullable', 'array'],
        ]);

        $data['key'] = $ignoreKey ?? (($data['key'] ?? '') ?: 'group_' . Str::random(10));

        $data['fields'] = $this->normalizeFields(
            is_array($request->input('fields')) ? $request->input('fields') : [],
            'fields'
        );

        return $data;
    }

    /**
     * Рекурсивная нормализация полей (включая подполя репитера).
     * Неизвестные ключи настроек сохраняются как есть — типы полей расширяемы.
     *
     * @return array<int, array>
     */
    protected function normalizeFields(array $fields, string $prefix, bool $allowRepeater = true): array
    {
        $types = $allowRepeater
            ? $this->types->names()
            : array_values(array_diff($this->types->names(), ['repeater']));

        $clean = [];
        $seenNames = [];

        foreach (array_values($fields) as $i => $field) {
            $path = "{$prefix}.{$i}";

            if (!is_array($field)) {
                throw ValidationException::withMessages([$path => 'Описание поля должно быть объектом.']);
            }

            $name = trim((string) ($field['name'] ?? ''));
            $type = trim((string) ($field['type'] ?? ''));
            $errors = [];

            if ($name === '' || !preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
                $errors["{$path}.name"] = 'Имя поля: латиница в нижнем регистре, цифры и «_», начинается с буквы.';
            } elseif (isset($seenNames[$name])) {
                $errors["{$path}.name"] = "Имя «{$name}» уже используется в этой группе.";
            }

            if ($type === '' || !in_array($type, $types, true)) {
                $errors["{$path}.type"] = 'Выберите корректный тип поля.';
            }

            if ($errors) {
                throw ValidationException::withMessages($errors);
            }

            $seenNames[$name] = true;

            $normalized = $field;
            $normalized['key'] = (string) ($field['key'] ?? '') ?: 'field_' . Str::random(12);
            $normalized['name'] = $name;
            $normalized['type'] = $type;
            $normalized['label'] = trim((string) ($field['label'] ?? ''));
            $normalized['instructions'] = trim((string) ($field['instructions'] ?? ''));
            $normalized['required'] = (bool) ($field['required'] ?? false);

            if ($type === 'repeater') {
                $sub = $field['sub_fields'] ?? [];
                $normalized['sub_fields'] = is_array($sub)
                    ? $this->normalizeFields($sub, "{$path}.sub_fields", false)
                    : [];
            } else {
                unset($normalized['sub_fields']);
            }

            $clean[] = $normalized;
        }

        return $clean;
    }

    protected function serializeGroup(array $group): array
    {
        $group['fields'] = array_map(
            fn (array $field) => $this->serializeField($field),
            $group['fields']
        );

        return $group;
    }

    protected function serializeField(array $field): array
    {
        $serialized = $this->types->make($field['type'] ?? 'text')->schema($field);

        if (($field['type'] ?? null) === 'repeater') {
            $serialized['sub_fields'] = array_map(
                fn (array $sub) => $this->serializeField($sub),
                $field['sub_fields'] ?? []
            );
        }

        return $serialized;
    }
}
