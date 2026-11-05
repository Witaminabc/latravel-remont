<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Тестовая страница «все типы полей» + назначение шаблона главной.
 *
 * Использование: php artisan db:seed --class=FieldsTestPageSeeder
 */
class FieldsTestPageSeeder extends Seeder
{
    public function run(): void
    {
        $model = config('pages.model', \App\Models\Post::class);

        // --- Главная: шаблон home (meta.template), если ещё не назначен ---
        $home = $model::query()
            ->where('type', 'page')
            ->where('slug', 'home')
            ->first();

        if ($home && empty($home->meta['template'])) {
            $home->meta = array_merge($home->meta ?? [], ['template' => 'home']);
            $home->save();
        }

        // --- Тестовая страница ---
        $page = $model::query()
            ->where('type', 'page')
            ->where('slug', 'test-fields')
            ->first();

        if (!$page) {
            $page = new $model(['slug' => 'test-fields']);
        }

        $authorId = $page->author_id ?? \App\Models\User::query()->min('id');
        if (!$authorId) {
            $this->command?->error('Нет пользователей (users) — создайте администратора перед прогоном сидера.');
            return;
        }

        $page->forceFill([
            'title' => 'Тест полей',
            'content' => '<p>Демо-страница <strong>teo/acf</strong>: по одному полю каждого типа.</p>',
            'type' => 'page',
            'status' => 'published',
            'author_id' => $authorId,
            'published_at' => now(),
            'meta' => array_merge($page->meta ?? [], ['template' => 'fields-test']),
        ]);
        $page->save();

        // --- Фикстуры значений (по одному на каждый тип поля) ---
        $image = !empty($page->featured_image)
            ? asset('storage/' . $page->featured_image)
            : (!empty($home?->featured_image) ? asset('storage/' . $home->featured_image) : null);

        $page->setAcf([
            'ft_text' => 'Привет, это текстовое поле!',
            'ft_textarea' => "Первая строка\nВторая строка текста",
            'ft_wysiwyg' => '<p>HTML с <strong>жирным</strong> и <em>курсивом</em>.</p>',
            'ft_number' => 42,
            'ft_select' => 'b',
            'ft_boolean' => true,
            'ft_image' => $image,
            'ft_items' => [
                [
                    'title' => 'Первый пункт',
                    'note' => 'Описание первого пункта',
                    'weight' => 10,
                    'flag' => true,
                ],
                [
                    'title' => 'Второй пункт',
                    'note' => 'Описание второго пункта',
                    'weight' => 5,
                    'flag' => false,
                ],
                [
                    'title' => 'Третий пункт',
                    'note' => 'Описание третьего пункта',
                    'weight' => 1,
                    'flag' => true,
                ],
            ],
        ]);

        // Инвалидация кэшей страниц (ключи общие с легаси-контроллером)
        Cache::forget('page_test-fields');
        Cache::forget('page_home');
    }
}