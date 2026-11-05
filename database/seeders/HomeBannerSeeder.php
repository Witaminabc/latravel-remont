<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

/**
 * Данные баннера-слайдера на главной (ACF-поле banner_slides).
 *
 * Repeater из 3 слайдов: title, subtitle, text, badge, button_label,
 * button_url, image. Значения берутся из database/seeders/data/home_banner.php.
 *
 * Использование: php artisan db:seed --class=HomeBannerSeeder
 * Повторный прогон перезаписывает значения banner_slides, отредактированные в админке.
 */
class HomeBannerSeeder extends Seeder
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

        $slides = require __DIR__ . '/data/home_banner.php';

        $home->setAcf([
            'banner_slides' => $slides,
        ]);

        // Инвалидация кэша страницы (общий ключ с PageTemplateController)
        Cache::forget('page_home');

        $this->command?->info(sprintf(
            'banner_slides: записано %d слайд(ов).',
            count($slides)
        ));
    }
}
