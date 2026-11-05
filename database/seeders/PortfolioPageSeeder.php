<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Teo\Acf\Models\FieldGroup;

class PortfolioPageSeeder extends Seeder
{
    public function run(): void
    {
        $model = config('pages.model', \App\Models\Post::class);

        $page = $model::query()
            ->where('type', 'page')
            ->where('slug', 'portfolio')
            ->first();

        if (!$page) {
            $page = new $model(['slug' => 'portfolio']);
        }

        $authorId = $page->author_id ?? \App\Models\User::query()->min('id');
        if (!$authorId) {
            $this->command?->error('Нет пользователей (users).');
            return;
        }

        $page->forceFill([
            'title' => 'Наши работы',
            'content' => '<p>Портфолио выполненных объектов.</p>',
            'type' => 'page',
            'status' => 'published',
            'author_id' => $authorId,
            'published_at' => now(),
            'meta' => array_merge($page->meta ?? [], ['template' => 'portfolio']),
        ]);
        $page->save();

        $portfolioPageId = $page->id;

        $items = require __DIR__ . '/data/portfolio.php';

        $page->setAcf([
            'portfolio_heading' => 'Портфолио',
            'portfolio_intro' => 'Каждое фото — реальный объект, сданный точно в срок.',
            'portfolio_items' => $items,
        ]);

        Cache::forget('page_portfolio');
        Cache::forget('page_home');

        // Создаём/обновляем ACF-группы в БД (чтобы админка видела поля)
        $this->ensureAcfGroup('group_portfolio', 'Портфолио / Наши работы', 'portfolio', [
            [
                'name' => 'portfolio_heading',
                'label' => 'Заголовок раздела',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'portfolio_intro',
                'label' => 'Вступительный текст',
                'type' => 'textarea',
            ],
            [
                'name' => 'portfolio_items',
                'label' => 'Объекты портфолио',
                'type' => 'repeater',
                'sub_fields' => [
                    ['name' => 'title', 'label' => 'Название объекта', 'type' => 'text', 'required' => true],
                    ['name' => 'slug', 'label' => 'Slug детальной страницы', 'type' => 'text'],
                    ['name' => 'address', 'label' => 'Адрес', 'type' => 'text'],
                    ['name' => 'area', 'label' => 'Площадь (м²)', 'type' => 'number'],
                    ['name' => 'term', 'label' => 'Сроки (дней)', 'type' => 'number'],
                    ['name' => 'price_works', 'label' => 'Стоимость работ', 'type' => 'number'],
                    ['name' => 'price_materials', 'label' => 'Стоимость материалов', 'type' => 'number'],
                    ['name' => 'image', 'label' => 'Изображение объекта', 'type' => 'image'],
                    ['name' => 'link_url', 'label' => 'Ссылка на детальную страницу', 'type' => 'text'],
                    ['name' => 'description', 'label' => 'Описание объекта', 'type' => 'textarea'],
                    [
                        'name' => 'gallery',
                        'label' => 'Галерея изображений',
                        'type' => 'repeater',
                        'sub_fields' => [
                            ['name' => 'image', 'label' => 'Изображение', 'type' => 'image'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->ensureAcfGroup('group_portfolio_item', 'Объект портфолио', 'portfolio-item', [
            [
                'name' => 'item_title',
                'label' => 'Название объекта',
                'type' => 'text',
                'required' => true,
            ],
            [
                'name' => 'item_address',
                'label' => 'Адрес',
                'type' => 'text',
            ],
            [
                'name' => 'item_area',
                'label' => 'Площадь (м²)',
                'type' => 'number',
            ],
            [
                'name' => 'item_term',
                'label' => 'Сроки (дней)',
                'type' => 'number',
            ],
            [
                'name' => 'item_price_works',
                'label' => 'Стоимость работ',
                'type' => 'number',
            ],
            [
                'name' => 'item_price_materials',
                'label' => 'Стоимость материалов',
                'type' => 'number',
            ],
            [
                'name' => 'item_image',
                'label' => 'Главное изображение',
                'type' => 'image',
            ],
            [
                'name' => 'item_description',
                'label' => 'Описание объекта',
                'type' => 'wysiwyg',
            ],
            [
                'name' => 'item_gallery',
                'label' => 'Галерея изображений',
                'type' => 'repeater',
                'sub_fields' => [
                    ['name' => 'image', 'label' => 'Изображение', 'type' => 'image'],
                ],
            ],
            [
                'name' => 'item_link_back',
                'label' => 'Ссылка назад к портфолио',
                'type' => 'text',
                'required' => true,
            ],
        ]);

        // Детальные страницы объектов (вложенные под portfolio)
        foreach ($items as $item) {
            $detailSlug = $item['slug'] ?? str($item['title'])->slug();

            $detail = $model::query()
                ->where('type', 'page')
                ->where('slug', $detailSlug)
                ->first();

            if (!$detail) {
                $detail = new $model(['slug' => $detailSlug]);
            }

            $detail->forceFill([
                'title' => $item['title'],
                'content' => '<p>Детальная страница объекта.</p>',
                'type' => 'page',
                'status' => 'published',
                'author_id' => $authorId,
                'published_at' => now(),
                'parent_id' => $portfolioPageId,
                'meta' => array_merge($detail->meta ?? [], ['template' => 'portfolio-item']),
            ]);
            $detail->save();

            $detail->setAcf([
                'item_title' => $item['title'],
                'item_address' => $item['address'] ?? '',
                'item_area' => $item['area'] ?? null,
                'item_term' => $item['term'] ?? null,
                'item_price_works' => $item['price_works'] ?? null,
                'item_price_materials' => $item['price_materials'] ?? null,
                'item_image' => $item['image'] ?? null,
                'item_gallery' => array_map(
                    fn ($src) => ['image' => $src],
                    $item['gallery'] ?? []
                ),
                'item_description' => $item['description'] ?? '',
                'item_link_back' => '/portfolio',
            ]);

            Cache::forget('page_' . $detailSlug);
        }

        $this->command?->info(sprintf('portfolio: записано %d объект(ов), %d детальных страниц (вложены под %d).', count($items), count($items), $portfolioPageId));
    }

    /**
     * Создаёт или обновляет ACF-группу в таблице acf_field_groups.
     */
    private function ensureAcfGroup(string $key, string $title, string $template, array $fields): void
    {
        $group = FieldGroup::query()->where('key', $key)->first();

        $data = [
            'key' => $key,
            'title' => $title,
            'location' => [
                ['type' => 'template', 'value' => $template],
            ],
            'fields' => $fields,
            'active' => true,
        ];

        if ($group) {
            $group->update($data);
        } else {
            FieldGroup::create($data);
        }
    }
}