<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class BlogPageSeeder extends Seeder
{
    public function run(): void
    {
        $model = config('pages.model', \App\Models\Post::class);

        // --- Страница блога /blog ---
        $page = $model::query()
            ->where('type', 'page')
            ->where('slug', 'blog')
            ->first();

        if (!$page) {
            $page = new $model(['slug' => 'blog']);
        }

        $authorId = $page->author_id ?? \App\Models\User::query()->min('id');
        if (!$authorId) {
            $this->command?->error('Нет пользователей (users).');
            return;
        }

        $page->forceFill([
            'title' => 'Блог',
            'content' => '<p>Полезные статьи о ремонте, интерьере и инженерных системах.</p>',
            'type' => 'page',
            'status' => 'published',
            'author_id' => $authorId,
            'published_at' => now(),
            'meta' => array_merge($page->meta ?? [], ['template' => 'blog']),
        ]);
        $page->save();

        $blogPageId = $page->id;

        // ACF для страницы разводной блога
        $page->setAcf([
            'blog_heading' => 'Блог',
            'blog_intro' => 'Рассказываем о ремонте, дизайне интерьеров и полезных решениях для дома.',
            'blog_items' => [
                [
                    'title' => 'Как спланировать гостиную с системой хранения: практичные идеи для квартиры',
                    'category' => 'Интерьер',
                    'date' => '14.08.2026',
                    'excerpt' => 'Разбираем, как зонировать гостиную, выбрать шкафы-купе и встроенные полки, чтобы вещей стало меньше, а места — больше.',
                    'image' => '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
                    'link_url' => '/blog/kak-splanirovat-gostinuyu-s-sistemoy-xraneniya',
                ],
                [
                    'title' => 'Ремонт квартиры с нуля: пошаговый план для собственника',
                    'category' => 'Ремонт',
                    'date' => '10.08.2026',
                    'excerpt' => 'От чертежей и сметы до финальной уборки — все этапы ремонта квартиры в правильном порядке, с советами по экономии.',
                    'image' => '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
                    'link_url' => '/blog/remont-kvartiry-s-nulya-poshagovyy-plan',
                ],
                [
                    'title' => 'Почему канализацию в загородном доме лучше доверять мастерам: 5 аргументов',
                    'category' => 'Инженерные системы',
                    'date' => '05.08.2026',
                    'excerpt' => 'Самостоятельный монтаж канализации в частном доме часто оборачивается протечками и переделками. Рассказываем, почему стоит обратиться к профессионалам.',
                    'image' => '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
                    'link_url' => '/blog/kanalizatsiya-v-zagorodnom-dome-5-argumentov',
                ],
            ],
        ]);

        Cache::forget('page_blog');

        // --- Детальные страницы статей (вложены под /blog) ---
        $articles = [
            [
                'slug' => 'kak-splanirovat-gostinuyu-s-sistemoy-xraneniya',
                'title' => 'Как спланировать гостиную с системой хранения: практичные идеи для квартиры',
                'date' => '14.08.2026',
                'category' => 'Интерьер',
                'image' => '/storage/uploads/portfolio/gotovyj-remont-2k-kvartiry-zhk-siberovo-kazan-17-800x600.jpg',
                'content' => '<p>Гостиная — это лицо квартиры. Здесь принимают гостей, отдыхают после работы и проводят время с семьёй. Но часто в этой комнате не хватает мест для хранения: вещи скапливаются на полках, столах и даже на полу.</p><h2>Зонирование пространства</h2><p>Определите зону отдыха, зону хранения и зону приёма гостей. Для хранения идеально подойдут шкафы-купе от пола до потолка, встроенные полки и пьедесталы под телевизор.</p><h2>Мебель-трансформер</h2><p>Используйте многофункциональную мебель: оттоманки с ящиками, журнальные столики с полками, диваны с коробами для белья. Так вы сохраните пространство и избавитесь от лишней мебели.</p>',
                'link_back' => '/blog',
            ],
            [
                'slug' => 'remont-kvartiry-s-nulya-poshagovyy-plan',
                'title' => 'Ремонт квартиры с нуля: пошаговый план для собственника',
                'date' => '10.08.2026',
                'category' => 'Ремонт',
                'image' => '/storage/uploads/portfolio/gotovyj-remont-dvuhkomnatnoj-kvartiry-zhk-merkurij-1-800x600.jpg',
                'content' => '<h2>Этап 1: Проект и смета</h2><p>Ремонт квартиры с нуля — это серьёзное испытание для любого собственника. Без чёткого плана легко запутаться в сроках, бюджете и выборе материалов.</p><p>Первый этап — замеры и проект. Определите перепланировку, расстановку мебели, розеток и выключателей. Составьте смету с запасом 15–20% на непредвиденные расходы.</p><h2>Этап 2: Черновая отделка</h2><p>Затем идёт черновая отделка: стяжка, штукатурка, электрика, сантехника. Только после высыхания всех мокрых процессов переходите к чистовой отделке: поклейка обоев, укладка пола, установка дверей.</p><h2>Этап 3: Чистовая отделка и декор</h2><p>Завершающий этап — расстановка мебели и декор. Не спешите: хороший ремонт делается не за неделю, но результат того стоит.</p>',
                'link_back' => '/blog',
            ],
            [
                'slug' => 'kanalizatsiya-v-zagorodnom-dome-5-argumentov',
                'title' => 'Почему канализацию в загородном доме лучше доверять мастерам: 5 аргументов',
                'date' => '05.08.2026',
                'category' => 'Инженерные системы',
                'image' => '/storage/uploads/portfolio/Ремонт-ванны-1-800x600.jpg',
                'content' => '<h2>Введение</h2><p>Самостоятельный монтаж канализации в частном доме кажется способом сэкономить. На практике ошибки в трубопроводах оборачиваются протечками, запахами и дорогостоящими переделками.</p><h3>1. Нарушение уклона труб</h3><p>Если труба уложена без нужного уклона, стоки застаиваются, образуются пробки и запахи. Профи знают точные нормы для каждого диаметра.</p><h3>2. Неправильный выбор материалов</h3><p>Для наружной и внутренней канализации нужны разные трубы. Использование не той марки полипропилена ведёт к трещинам при заморозке.</p><h3>3. Отсутствие вентиляции</h3><p>Без вентиляционного стояка в системе возникает вакуум, и вода из сифонов вытягивается — в дом попадает канализационный запах.</p><h3>4. Ошибки при установке сантехники</h3><p>Некорректное подключение унитаза и раковины приводит к протечкам под плиткой и повреждению перекрытий.</p><h3>5. Отсутствие гарантии</h3><p>Мастера дают гарантию на монтаж. При самостоятельной установке все риски ложатся на вас.</p><p>Доверьте канализацию профессионалам — и забудьте о проблемах на долгие годы.</p>',
                'link_back' => '/blog',
            ],
        ];

        foreach ($articles as $article) {
            $detailSlug = $article['slug'];

            $detail = $model::query()
                ->where('type', 'page')
                ->where('slug', $detailSlug)
                ->first();

            if (!$detail) {
                $detail = new $model(['slug' => $detailSlug]);
            }

            $detail->forceFill([
                'title' => $article['title'],
                'content' => '<p>Детальная страница статьи.</p>',
                'type' => 'page',
                'status' => 'published',
                'author_id' => $authorId,
                'published_at' => now(),
                'parent_id' => $blogPageId,
                'meta' => array_merge($detail->meta ?? [], ['template' => 'blog-item']),
            ]);
            $detail->save();

            $detail->setAcf([
                'item_title' => $article['title'],
                'item_date' => $article['date'],
                'item_category' => $article['category'],
                'item_image' => $article['image'] ?? null,
                'item_content' => $article['content'],
                'item_link_back' => $article['link_back'],
            ]);

            Cache::forget('page_' . $detailSlug);
        }

        $this->command?->info(sprintf('blog: записана страница /blog и %d детальных страниц статей.', count($articles)));
    }
}