<?php

namespace Teo\Acf\Support;

use Illuminate\Support\Facades\Schema;
use Teo\Acf\Models\FieldGroup;

/**
 * Хранилище групп полей в БД + гидрация реестра.
 *
 * Правила:
 *  - группы, зарегистрированные в коде (Acf::addGroup), — базовая линия (baseline);
 *  - строка в БД с active=true переопределяет одноимённую группу из кода;
 *  - строка с active=false скрывает группу (для кодовых групп — «мягкое удаление»,
 *    т.к. код зарегистрирует её заново при каждом запросе);
 *  - группы, созданные в админке, при удалении стираются из БД полностью.
 */
class FieldGroupRepository
{
    /** @var array<string, array>|null Снимок кодовых групп (ключ => группа). */
    protected ?array $baseline = null;

    /** @var array<int, string> Ключи, существующие в коде. */
    protected array $codeKeys = [];

    /**
     * Загрузить группы из БД поверх кодовых (вызывается в boot контроллера/провайдера).
     */
    public function hydrate(FieldGroupRegistry $registry): void
    {
        $this->captureBaseline($registry);

        $registry->reset($this->baseline ?? []);

        $table = config('acf.groups_table', 'acf_field_groups');

        if (!Schema::hasTable($table)) {
            return;
        }

        foreach (FieldGroup::query()->orderBy('id')->get() as $row) {
            if (!$row->active) {
                $registry->removeGroup($row->key);
                continue;
            }

            $registry->addGroup([
                'key' => $row->key,
                'title' => $row->title,
                'location' => $row->location ?? [],
                'fields' => $row->fields ?? [],
                'source' => 'database',
            ]);
        }
    }

    /** @return \Illuminate\Support\Collection<int, FieldGroup> */
    public function all()
    {
        return FieldGroup::query()->orderBy('id')->get();
    }

    public function find(string $key): ?FieldGroup
    {
        return FieldGroup::query()->where('key', $key)->first();
    }

    /** @param array<string, mixed> $data */
    public function create(array $data): FieldGroup
    {
        return FieldGroup::create($this->persistable($data));
    }

    /** @param array<string, mixed> $data */
    public function update(string $key, array $data): FieldGroup
    {
        $row = $this->find($key);

        if (!$row) {
            // Группа из кода — создаём строку-переопределение.
            return FieldGroup::create($this->persistable($data));
        }

        $row->update($this->persistable($data));

        return $row->refresh();
    }

    public function delete(string $key): void
    {
        $row = $this->find($key);

        if ($row && !$this->isCodeGroup($key)) {
            $row->delete();

            return;
        }

        if ($row) {
            $row->update(['active' => false]);

            return;
        }

        // Кодовая группа без строки в БД — создаём tombstone, иначе она вернётся из кода.
        FieldGroup::create([
            'key' => $key,
            'title' => '',
            'location' => [],
            'fields' => [],
            'active' => false,
        ]);
    }

    public function isCodeGroup(string $key): bool
    {
        return in_array($key, $this->codeKeys, true);
    }

    /**
     * Первый вызов в процессе фиксирует кодовые группы как базовую линию.
     * Поздно зарегистрированные в коде группы доснапшочиваются при каждом hydrate.
     */
    protected function captureBaseline(FieldGroupRegistry $registry): void
    {
        foreach ($registry->groups() as $group) {
            if (($group['source'] ?? 'code') === 'code') {
                $this->baseline[$group['key']] = $group;
            }
        }

        $this->codeKeys = array_keys($this->baseline ?? []);
    }

    /** @param array<string, mixed> $data @return array<string, mixed> */
    protected function persistable(array $data): array
    {
        return [
            'key' => $data['key'],
            'title' => $data['title'],
            'location' => $data['location'] ?? [],
            'fields' => $data['fields'] ?? [],
            'active' => $data['active'] ?? true,
        ];
    }
}