<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Устанавливает шаблон 'about' для существующей страницы about-us.
 *
 * Использование: php artisan db:seed --class=AboutTemplateSeeder
 */
class AboutTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $model = config('pages.model', \App\Models\Post::class);

        $about = $model::query()
            ->where('type', 'page')
            ->where('slug', 'about-us')
            ->first();

        if (!$about) {
            $this->command?->error('Страница about-us не найдена.');
            return;
        }

        $about->meta = array_merge($about->meta ?? [], ['template' => 'about']);
        $about->save();

        $this->command?->info('Шаблон about-us установлен: about');
    }
}