<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Данные блока «Штат мастеров» на главной (ACF-поле masters).
 *
 * Значения берутся из database/seeders/data/home_masters.php.
 *
 * Использование: php artisan db:seed --class=HomeMastersSeeder
 * Повторный прогон перезаписывает значения masters, отредактированные в админке.
 */
class HomeMastersSeeder extends Seeder
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

        $masters = require __DIR__ . '/data/home_masters.php';

        $home->setAcf([
            'masters' => $masters,
        ]);

        // Инвалидация кэша страницы (общий ключ с PageTemplateController)
        Cache::forget('page_home');

        $this->command?->info(sprintf(
            'masters: записано %d мастер(ов).',
            count($masters)
        ));
    }
}