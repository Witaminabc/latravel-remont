<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Данные таблицы прайсов на главной (ACF-поле price_sections).
 *
 * Трёхуровневая структура (как в источнике remontoff73.ru):
 *   раздел -> подраздел -> строки [{ name, unit, price }].
 * Значения берутся из database/seeders/data/home_prices.php.
 *
 * Использование: php artisan db:seed --class=HomePriceSeeder
 * Повторный прогон перезаписывает значения price_sections, отредактированные в админке.
 */
class HomePriceSeeder extends Seeder
{
    public function run(): void
    {
        $model = config('pages.model', \App\Models\Post::class);

        $home = $model::query()
            ->where('type', 'page')
            ->where('slug', 'home')
            ->first();

        if (!$home) {
            $this->command?->error('Страница home не найдена — сначала создайте её (см. FieldsTestPageSeeder).');
            return;
        }

        $sections = require __DIR__ . '/data/home_prices.php';

        $home->setAcf([
            'price_sections' => $sections,
        ]);

        // Инвалидация кэша страницы (общий ключ с PageTemplateController)
        Cache::forget('page_home');

        $rowsTotal = 0;
        foreach ($sections as $section) {
            foreach ($section['subsections'] ?? [] as $sub) {
                $rowsTotal += count($sub['rows'] ?? []);
            }
        }

        $this->command?->info(sprintf(
            'price_sections: записано %d раздел(ов), %d строк.',
            count($sections),
            $rowsTotal
        ));
    }
}

