<?php
// database/seeders/PostSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run()
    {
        // 1. Создаем таксономии (категории и теги)
        $this->createTaxonomies();

        // 2. Создаем посты
        $this->createPosts();

        // 3. Связываем посты с таксономиями
        $this->attachTaxonomiesToPosts();
    }

    private function createTaxonomies()
    {
        // Категории
        $categories = [
            ['name' => 'Новости', 'slug' => 'news', 'type' => 'category', 'description' => 'Новости проекта'],
            ['name' => 'Статьи', 'slug' => 'articles', 'type' => 'category', 'description' => 'Полезные статьи'],
            ['name' => 'Обзоры', 'slug' => 'reviews', 'type' => 'category', 'description' => 'Обзоры продуктов'],
            ['name' => 'Видео', 'slug' => 'video', 'type' => 'category', 'description' => 'Видеоконтент'],
            ['name' => 'Новости компании', 'slug' => 'company-news', 'type' => 'category', 'description' => 'Новости и события компании'],
            ['name' => 'Туториалы', 'slug' => 'tutorials', 'type' => 'category', 'description' => 'Обучающие материалы'],
            ['name' => 'Новости индустрии', 'slug' => 'industry-news', 'type' => 'category', 'description' => 'Новости из мира IT'],
            ['name' => 'События', 'slug' => 'events', 'type' => 'category', 'description' => 'Мероприятия и встречи'],
        ];

        foreach ($categories as $category) {
            DB::table('taxonomies')->insert(array_merge($category, [
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Подкатегории
        $subcategories = [
            ['name' => 'Laravel', 'slug' => 'laravel', 'type' => 'category', 'parent_id' => 2, 'description' => 'Все о Laravel'],
            ['name' => 'PHP', 'slug' => 'php', 'type' => 'category', 'parent_id' => 2, 'description' => 'PHP разработка'],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'type' => 'category', 'parent_id' => 2, 'description' => 'Vue.js компоненты'],
            ['name' => 'React', 'slug' => 'react', 'type' => 'category', 'parent_id' => 2, 'description' => 'React разработка'],
            ['name' => 'Обзоры ПО', 'slug' => 'software-reviews', 'type' => 'category', 'parent_id' => 3, 'description' => 'Обзоры программного обеспечения'],
            ['name' => 'Обзоры игр', 'slug' => 'game-reviews', 'type' => 'category', 'parent_id' => 3, 'description' => 'Обзоры видеоигр'],
            ['name' => 'Скринкасты', 'slug' => 'screencasts', 'type' => 'category', 'parent_id' => 4, 'description' => 'Видеоуроки'],
            ['name' => 'Интервью', 'slug' => 'interviews', 'type' => 'category', 'parent_id' => 1, 'description' => 'Интервью с экспертами'],
        ];

        foreach ($subcategories as $subcategory) {
            DB::table('taxonomies')->insert(array_merge($subcategory, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        // Теги
        $tags = [
            ['name' => 'PHP', 'slug' => 'php-tag', 'type' => 'tag', 'description' => 'PHP программирование'],
            ['name' => 'Laravel', 'slug' => 'laravel-tag', 'type' => 'tag', 'description' => 'Laravel фреймворк'],
            ['name' => 'JavaScript', 'slug' => 'javascript', 'type' => 'tag', 'description' => 'JavaScript разработка'],
            ['name' => 'Vue.js', 'slug' => 'vuejs-tag', 'type' => 'tag', 'description' => 'Vue.js'],
            ['name' => 'React', 'slug' => 'react-tag', 'type' => 'tag', 'description' => 'React'],
            ['name' => 'MySQL', 'slug' => 'mysql', 'type' => 'tag', 'description' => 'Базы данных MySQL'],
            ['name' => 'Docker', 'slug' => 'docker', 'type' => 'tag', 'description' => 'Контейнеризация'],
            ['name' => 'API', 'slug' => 'api', 'type' => 'tag', 'description' => 'API разработка'],
            ['name' => 'Frontend', 'slug' => 'frontend', 'type' => 'tag', 'description' => 'Фронтенд разработка'],
            ['name' => 'Backend', 'slug' => 'backend', 'type' => 'tag', 'description' => 'Бэкенд разработка'],
            ['name' => 'DevOps', 'slug' => 'devops', 'type' => 'tag', 'description' => 'DevOps практики'],
            ['name' => 'Security', 'slug' => 'security', 'type' => 'tag', 'description' => 'Безопасность'],
            ['name' => 'Performance', 'slug' => 'performance', 'type' => 'tag', 'description' => 'Оптимизация производительности'],
            ['name' => 'Testing', 'slug' => 'testing', 'type' => 'tag', 'description' => 'Тестирование'],
            ['name' => 'Git', 'slug' => 'git', 'type' => 'tag', 'description' => 'Система контроля версий'],
        ];

        foreach ($tags as $tag) {
            DB::table('taxonomies')->insert(array_merge($tag, [
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    private function createPosts()
    {
        $posts = [
            [
                'title' => 'Введение в Laravel 11',
                'slug' => 'introduction-to-laravel-11',
                'content' => '<h2>Что нового в Laravel 11?</h2><p>Laravel 11 приносит множество улучшений...</p><p>Основные изменения включают упрощенную структуру приложения, новые возможности для разработки API и улучшенную производительность.</p><h3>Ключевые особенности:</h3><ul><li>Упрощенная структура bootstrap</li><li>Новые методы для работы с SQL</li><li>Улучшенная обработка очередей</li></ul>',
                'excerpt' => 'Обзор новых возможностей Laravel 11 и что это значит для разработчиков',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 1,
                'order' => 1,
                'parent_id' => null,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Создание чата на Laravel Reverb',
                'slug' => 'real-time-chat-with-laravel-reverb',
                'content' => '<h2>Введение в WebSockets</h2><p>Laravel Reverb предоставляет простой способ добавить real-time функциональность...</p><p>Мы создадим полноценный чат с нуля.</p><pre><code>php artisan reverb:start</code></pre><p>Настройка событий и очередей...</p>',
                'excerpt' => 'Пошаговое руководство по созданию real-time чата с использованием Laravel Reverb и WebSockets',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 1,
                'order' => 2,
                'parent_id' => null,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Оптимизация запросов MySQL',
                'slug' => 'mysql-query-optimization',
                'content' => '<h2>Индексы и их типы</h2><p>Правильное использование индексов может ускорить запросы в десятки раз...</p><p>Рассмотрим основные стратегии оптимизации.</p>',
                'excerpt' => 'Техники оптимизации MySQL запросов для высоконагруженных проектов',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 1,
                'order' => 3,
                'parent_id' => null,
                'published_at' => now()->subDays(15),
            ],
            [
                'title' => 'Анонс новой версии',
                'slug' => 'new-version-announcement',
                'content' => '<p>Рады сообщить о выходе новой версии нашего продукта...</p><p>Добавлены долгожданные функции.</p>',
                'excerpt' => 'Анонс выхода новой версии с улучшениями и новыми возможностями',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 2,
                'order' => 4,
                'parent_id' => null,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Обзор PhpStorm 2024',
                'slug' => 'phpstorm-2024-review',
                'content' => '<h2>Новые возможности IDE</h2><p>PhpStorm 2024 получил множество улучшений для Laravel разработки...</p>',
                'excerpt' => 'Полный обзор новой версии PhpStorm с фокусом на Laravel инструменты',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 1,
                'order' => 5,
                'parent_id' => null,
                'published_at' => now()->subDay(),
            ],
            [
                'title' => 'Черная пятница 2024',
                'slug' => 'black-friday-2024',
                'content' => '<p>Скидки до 50% на все курсы и подписки...</p><p>Акция действует до конца ноября.</p>',
                'excerpt' => 'Специальные предложения на черную пятницу для всех клиентов',
                'type' => 'post',
                'status' => 'draft',
                'author_id' => 2,
                'order' => 0,
                'parent_id' => null,
                'published_at' => null,
            ],
            [
                'title' => 'Как стать senior разработчиком',
                'slug' => 'how-to-become-senior-developer',
                'content' => '<h2>Путь от junior до senior</h2><p>Делимся опытом и планом развития...</p><h3>Необходимые навыки:</h3><ul><li>Архитектура ПО</li><li>Системное мышление</li><li>Наставничество</li></ul>',
                'excerpt' => 'Практические советы по профессиональному росту в IT индустрии',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 3,
                'order' => 6,
                'parent_id' => null,
                'published_at' => now()->subDays(20),
            ],
            [
                'title' => 'Страница о нас',
                'slug' => 'about-us',
                'content' => '<p>Мы команда профессионалов, создающих качественный софт с 2020 года...</p><p>Наша миссия - делать разработку доступной и понятной.</p>',
                'excerpt' => 'Информация о компании и команде',
                'type' => 'page',
                'status' => 'published',
                'author_id' => 1,
                'order' => 1,
                'parent_id' => null,
                'published_at' => now()->subDays(30),
                'meta' => ['template' => 'about'],
            ],
            [
                'title' => 'Политика конфиденциальности',
                'slug' => 'privacy-policy',
                'content' => '<p>Политика обработки персональных данных...</p>',
                'excerpt' => 'Как мы собираем и используем ваши данные',
                'type' => 'page',
                'status' => 'published',
                'author_id' => 1,
                'order' => 2,
                'parent_id' => null,
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'Обработка изображений в Laravel',
                'slug' => 'image-processing-in-laravel',
                'content' => '<h2>Использование Intervention Image</h2><p>Библиотека для работы с изображениями предоставляет простой API...</p>',
                'excerpt' => 'Урок по обработке и оптимизации изображений в Laravel проектах',
                'type' => 'post',
                'status' => 'draft',
                'author_id' => 3,
                'order' => 0,
                'parent_id' => null,
                'published_at' => null,
            ],
            [
                'title' => 'Как спланировать гостиную с системой хранения: практичные идеи для квартиры',
                'slug' => 'kak-splanirovat-gostinuyu-s-sistemoy-xraneniya',
                'content' => '<h2>Введение</h2><p>Гостиная — это лицо квартиры. Здесь принимают гостей, отдыхают после работы и проводят время с семьёй. Но часто в этой комнате не хватает мест для хранения: вещи скапливаются на полках, столах и даже на полу.</p><h2>Зонирование пространства</h2><p>Определите зону отдыха, зону хранения и зону приёма гостей. Для хранения идеально подойдут шкафы-купе от пола до потолка, встроенные полки и пьедесталы под телевизор.</p><h2>Мебель-трансформер</h2><p>Используйте multifunkcionalnuyu mebel: оттоманки с ящиками, журнальные столики с полками, диваны с коробами для белья. Так вы сохраните пространство и избавитесь от лишней мебели.</p>',
                'excerpt' => 'Разбираем, как зонировать гостиную, выбрать шкафы-купе и встроенные полки, чтобы вещей стало меньше, а места — больше.',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 1,
                'order' => 7,
                'parent_id' => null,
                'published_at' => now()->subDays(30),
            ],
            [
                'title' => 'Ремонт квартиры с нуля: пошаговый план для собственника',
                'slug' => 'remont-kvartiry-s-nulya-poshagovyy-plan',
                'content' => '<h2>Этап 1: Проект и сметa</h2><p>Ремонт квартиры с нуля — это серьезное испытание для любого собственника. Без чёткого плана легко запутаться в сроках, бюджете и выборах материалов.</p><p>Первый этап — замеры и проект. Определите перепланировку, расстановку мебели,位置 розеток и выключателей. Составьте смету с запасом 15–20% на непредвиденные расходы.</p><h2>Этап 2: Черновая отделка</h2><p>Затем идет черновая отделка: стяжка, штукатурка, электрика, сантехника. Только после высыхания всех мокрых процессов переходите к чистовой отделке: поклейка обоев, укладка пола, установка дверей.</p><h2>Этап 3: Чистовая отделка и декор</h2><p>Завершающий этап — расстановка мебели и декор. Не спешите: хороший ремонт делается не за неделю, но результат того стоит.</p>',
                'excerpt' => 'От чертежей и сметы до финальной уборки — все этапы ремонта квартиры в правильном порядке, с советами по экономии.',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 1,
                'order' => 8,
                'parent_id' => null,
                'published_at' => now()->subDays(25),
            ],
            [
                'title' => 'Почему канализацию в загородном доме лучше доверять мастерам: 5 аргументов',
                'slug' => 'kanalizatsiya-v-zagorodnom-dome-5-argumentov',
                'content' => '<h2>Введение</h2><p>Самостоятельный монтаж канализации в частном доме кажется способом сэкономить. На практике ошибки в трубопроводах оборачиваются протечками, запахами и дорогостоящими переделками.</p><h3>1. Нарушение уклона труб</h3><p>Если труба уложена без нужного уклона, стоки застаиваются, образуются пробки и запахи. Профи знают точные нормы для каждого диаметра.</p><h3>2. Неправильный выбор материалов</h3><p>Для наружной и внутренней канализации нужны разные трубы. Использование не той марки полипропилена ведет к трещинам при заморозке.</p><h3>3. Отсутствие вентиляции</h3><p>Без вентиляционного стояка в системе возникает вакуум, и вода из сифонов вытягивается — в дом попадает канализационный запах.</p><h3>4. Ошибки при установке сантехники</h3><p>Некорректное подключение унитаза и раковины приводит к протечкам под плиткой и повреждению перекрытий.</p><h3>5. Отсутствие гарантии</h3><p>Мастера дают гарантию на монтаж. При самостоятельной установке все риски ложатся на вас.</p><p>Доверьте канализацию профессионалам — и забудьте о проблемах на долгие годы.</p>',
                'excerpt' => 'Самостоятельный монтаж канализации в частном доме часто оборачивается протечками и переделками. Рассказываем, почему стоит обратиться к профессионалам.',
                'type' => 'post',
                'status' => 'published',
                'author_id' => 1,
                'order' => 9,
                'parent_id' => null,
                'published_at' => now()->subDays(20),
            ],
        ];

        foreach ($posts as $post) {
            // Генерируем мета-данные
            $post['meta'] = json_encode([
                'seo_title' => $post['title'] . ' | Блог',
                'seo_description' => $post['excerpt'],
                'views' => rand(100, 5000),
                'likes' => rand(10, 500),
                'reading_time' => rand(3, 15) . ' мин',
            ]);

            // Добавляем featured_image для некоторых постов
            if (rand(0, 1)) {
                $post['featured_image'] = 'https://picsum.photos/800/400?random=' . rand(1, 1000);
            }

            DB::table('posts')->insert($post);
        }

        // Добавляем черновик текущего пользователя (если нет постов от автора 4)
        DB::table('posts')->insert([
            'title' => 'Мой первый черновик',
            'slug' => 'my-first-draft',
            'content' => '<p>Это черновик, который еще не опубликован...</p><p>Здесь могут быть любые мысли и заметки.</p>',
            'excerpt' => 'Личные заметки и идеи для будущих статей',
            'type' => 'post',
            'status' => 'draft',
            'author_id' => 4,
            'meta' => json_encode(['draft_notes' => 'Нужно дописать раздел про...']),
            'order' => 0,
            'parent_id' => null,
            'published_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function attachTaxonomiesToPosts()
    {
        // Получаем все посты и таксономии
        $posts = DB::table('posts')->pluck('id')->toArray();
        $taxonomies = DB::table('taxonomies')->pluck('id', 'slug')->toArray();

        // Связи: post_id => [taxonomy_slugs]
        $relations = [
            1 => ['laravel', 'php-tag', 'laravel-tag', 'backend'], // Laravel пост
            2 => ['laravel', 'reviews', 'laravel-tag', 'api', 'backend'], // Reverb чат
            3 => ['mysql', 'php-tag', 'performance'], // MySQL оптимизация
            4 => ['news', 'company-news', 'events'], // Анонс
            5 => ['software-reviews', 'reviews', 'php-tag'], // Обзор PhpStorm
            6 => ['news', 'events'], // Черная пятница
            7 => ['articles', 'tutorials', 'laravel', 'php-tag', 'testing'], // Senior разработчик
            8 => ['page'], // О нас (как страница, без тегов)
            9 => ['page'], // Политика конфиденциальности
            10 => ['tutorials', 'php-tag', 'laravel-tag', 'frontend'], // Обработка изображений
            11 => ['articles', 'tutorials', 'laravel', 'php-tag'], // Как спланировать гостиную
            12 => ['articles', 'tutorials', 'php-tag'], // Ремонт квартиры с нуля
            13 => ['articles', 'tutorials', 'php-tag'], // Канализация в загородном доме
            14 => ['draft'], // Черновик (тег для черновиков, нужно создать)
        ];

        // Создаем тег "draft" если его нет
        if (!isset($taxonomies['draft'])) {
            $draftTagId = DB::table('taxonomies')->insertGetId([
                'name' => 'Draft',
                'slug' => 'draft',
                'type' => 'tag',
                'description' => 'Черновики',
                'parent_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $taxonomies['draft'] = $draftTagId;
        }

        $order = 0;
        foreach ($relations as $postId => $taxonomySlugs) {
            foreach ($taxonomySlugs as $slug) {
                if (isset($taxonomies[$slug])) {
                    DB::table('post_taxonomy')->insert([
                        'post_id' => $postId,
                        'taxonomy_id' => $taxonomies[$slug],
                        'order' => $order++,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        // Добавляем случайные теги к постам для разнообразия
        $allTags = DB::table('taxonomies')->where('type', 'tag')->pluck('id')->toArray();

        foreach ($posts as $postId) {
            // Пропускаем уже обработанные посты
            $existing = DB::table('post_taxonomy')->where('post_id', $postId)->exists();
            if (!$existing && rand(0, 3) > 0) { // 75% шанс добавить теги
                $randomTags = array_rand(array_flip($allTags), rand(1, 3));
                $randomTags = is_array($randomTags) ? $randomTags : [$randomTags];

                foreach ($randomTags as $tagId) {
                    DB::table('post_taxonomy')->insert([
                        'post_id' => $postId,
                        'taxonomy_id' => $tagId,
                        'order' => rand(0, 100),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }
}
