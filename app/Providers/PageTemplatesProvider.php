<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Teo\Acf\Facades\Acf;
use Teo\Pages\Support\TemplateRegistry;

/**
 * Регистрация шаблонов публичных страниц приложения.
 *
 * Шаблон = Vue-компонент из resources/js/templates/*.vue со статическим
 * составом секций; группы — ключи ACF-групп блоков шаблона (для панели
 * блоков в админке и валидации незарегистрированных групп).
 */
class PageTemplatesProvider extends ServiceProvider
{
    public function boot(): void
    {
        // --- Регистрация ACF-групп для портфолио (кодовая база línea) ---
        Acf::addGroup([
            'key' => 'group_portfolio',
            'title' => 'Портфолио / Наши работы',
            'location' => [
                ['type' => 'template', 'value' => 'portfolio'],
            ],
            'fields' => [
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
                        ['name' => 'address', 'label' => 'Адрес', 'type' => 'text'],
                        ['name' => 'area', 'label' => 'Площадь (м²)', 'type' => 'number'],
                        ['name' => 'term', 'label' => 'Сроки (дней)', 'type' => 'number'],
                        ['name' => 'price_works', 'label' => 'Стоимость работ', 'type' => 'number'],
                        ['name' => 'price_materials', 'label' => 'Стоимость материалов', 'type' => 'number'],
                        ['name' => 'image', 'label' => 'Изображение объекта', 'type' => 'image'],
                        ['name' => 'link_url', 'label' => 'Ссылка на детальную страницу', 'type' => 'text'],
                    ],
                ],
            ],
        ]);

        Acf::addGroup([
            'key' => 'group_portfolio_item',
            'title' => 'Объект портфолио',
            'location' => [
                ['type' => 'template', 'value' => 'portfolio-item'],
            ],
            'fields' => [
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
            ],
        ]);

        // --- Регистрация ACF-групп для блога ---
        Acf::addGroup([
            'key' => 'group_blog',
            'title' => 'Блог (разводная)',
            'location' => [
                ['type' => 'template', 'value' => 'blog'],
            ],
            'fields' => [
                [
                    'name' => 'blog_heading',
                    'label' => 'Заголовок раздела',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'blog_intro',
                    'label' => 'Вступительный текст',
                    'type' => 'textarea',
                ],
                [
                    'name' => 'blog_items',
                    'label' => 'Статьи',
                    'type' => 'repeater',
                    'sub_fields' => [
                        ['name' => 'title', 'label' => 'Заголовок статьи', 'type' => 'text', 'required' => true],
                        ['name' => 'category', 'label' => 'Категория', 'type' => 'text'],
                        ['name' => 'date', 'label' => 'Дата публикации', 'type' => 'date'],
                        ['name' => 'excerpt', 'label' => 'Анонс', 'type' => 'textarea'],
                        ['name' => 'image', 'label' => 'Изображение', 'type' => 'image'],
                        ['name' => 'link_url', 'label' => 'Ссылка на статью', 'type' => 'text'],
                    ],
                ],
            ],
        ]);

        Acf::addGroup([
            'key' => 'group_blog_item',
            'title' => 'Статья (детальная страница)',
            'location' => [
                ['type' => 'template', 'value' => 'blog-item'],
            ],
            'fields' => [
                [
                    'name' => 'item_title',
                    'label' => 'Заголовок статьи',
                    'type' => 'text',
                    'required' => true,
                ],
                [
                    'name' => 'item_date',
                    'label' => 'Дата публикации',
                    'type' => 'date',
                ],
                [
                    'name' => 'item_category',
                    'label' => 'Категория',
                    'type' => 'text',
                ],
                [
                    'name' => 'item_image',
                    'label' => 'Главное изображение',
                    'type' => 'image',
                ],
                [
                    'name' => 'item_content',
                    'label' => 'Полный текст статьи',
                    'type' => 'wysiwyg',
                ],
                [
                    'name' => 'item_link_back',
                    'label' => 'Ссылка назад к блогу',
                    'type' => 'text',
                    'required' => true,
                ],
            ],
        ]);

        $registry = $this->app->make(TemplateRegistry::class);

        $registry->add([
            'key' => 'default',
            'title' => 'Стандартная страница',
            'component' => 'templates/Default',
            'types' => ['page', 'post'],
            'description' => 'Заголовок и контент страницы.',
            'default' => true,
            'groups' => [],
        ]);


        $registry->add([
            'key' => 'home',
            'title' => 'Главная страница',
            'component' => 'templates/Home',
            'types' => ['page'],
            'description' => 'Hero, баннер-слайдер (banner_slides), преимущества (repeater), тарифы (tariffs), прайс-лист (price_sections), квиз (quiz_steps) и контент главной.',
            'groups' => ['group_home', 'group_home_tariffs', 'group_home_masters', 'group_home_quiz'],
        ]);

        $registry->add([
            'key' => 'fields-test',
            'title' => 'Тест полей',
            'component' => 'templates/FieldsTest',
            'types' => ['page'],
            'description' => 'Демо-шаблон: по одному полю каждого типа teo/acf.',
            'groups' => ['group_fields_test'],
        ]);
        $registry->add([
            'key' => 'portfolio',
            'title' => 'Портфолио / Наши работы',
            'component' => 'templates/Portfolio',
            'types' => ['page'],
            'description' => 'Галерея выполненных объектов с карточками и детальной страницей.',
            'groups' => ['group_portfolio'],
        ]);
        $registry->add([
            'key' => 'portfolio-item',
            'title' => 'Объект портфолио',
            'component' => 'templates/PortfolioItem',
            'types' => ['page'],
            'description' => 'Детальная страница объекта портфолио.',
            'groups' => ['group_portfolio_item'],
        ]);
        $registry->add([
            'key' => 'contacts',
            'title' => 'Контакты',
            'component' => 'templates/Contacts',
            'types' => ['page'],
            'description' => 'Контакты: адрес, телефон, e-mail, режим работы и карта (по координатам или готовой embed-ссылке).',
            'groups' => ['group_contacts'],
        ]);

        $registry->add([
            'key' => 'about',
            'title' => 'О компании',
            'component' => 'templates/About',
            'types' => ['page'],
            'description' => 'Страница «О нас»: история компании, цифры, стандарты качества, штат мастеров.',
            'groups' => ['group_about'],
        ]);

        $registry->add([
            'key' => 'blog',
            'title' => 'Блог',
            'component' => 'templates/Blog',
            'types' => ['page'],
            'description' => 'Разводная блога: сетка карточек статей.',
            'groups' => ['group_blog'],
        ]);
        $registry->add([
            'key' => 'blog-item',
            'title' => 'Статья',
            'component' => 'templates/BlogItem',
            'types' => ['page'],
            'description' => 'Детальная страница статьи блога.',
            'groups' => ['group_blog_item'],
        ]);
    }
}
