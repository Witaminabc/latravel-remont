<?php
// database/seeders/SettingSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run()
    {
        $settings = [
            // Основные настройки сайта
            [
                'key' => 'site_name',
                'value' => 'Laravel CMS',
            ],
            [
                'key' => 'site_description',
                'value' => 'Мощная CMS на Laravel с поддержкой Real-time чата',
            ],
            [
                'key' => 'site_keywords',
                'value' => 'laravel, cms, blog, real-time, chat, websocket',
            ],
            [
                'key' => 'site_logo',
                'value' => '/images/logo.png',
            ],
            [
                'key' => 'site_favicon',
                'value' => '/favicon.ico',
            ],

            // Контакты
            [
                'key' => 'contact_email',
                'value' => 'admin@example.com',
            ],
            [
                'key' => 'contact_phone',
                'value' => '+7 (999) 123-45-67',
            ],
            [
                'key' => 'contact_address',
                'value' => 'г. Москва, ул. Примерная, д. 123',
            ],

            // Социальные сети
            [
                'key' => 'social_telegram',
                'value' => 'https://t.me/chat',
            ],
            [
                'key' => 'social_max',
                'value' => 'https://max.ru/chat',
            ],
            [
                'key' => 'social_vk',
                'value' => 'https://vk.com/chat',
            ],
            [
                'key' => 'social_whatsapp',
                'value' => 'https://wa.me/79991234567',
            ],
            [
                'key' => 'social_youtube',
                'value' => 'https://youtube.com/@chat',
            ],
            [
                'key' => 'social_github',
                'value' => 'https://github.com/chat',
            ],

            // Настройки блога
            [
                'key' => 'posts_per_page',
                'value' => '10',
            ],
            [
                'key' => 'enable_comments',
                'value' => 'true',
            ],
            [
                'key' => 'moderate_comments',
                'value' => 'false',
            ],
            [
                'key' => 'default_post_status',
                'value' => 'draft',
            ],

            // Настройки чата
            [
                'key' => 'chat_enabled',
                'value' => 'true',
            ],
            [
                'key' => 'chat_messages_per_page',
                'value' => '50',
            ],
            [
                'key' => 'chat_file_upload_enabled',
                'value' => 'true',
            ],
            [
                'key' => 'chat_max_file_size',
                'value' => '5242880', // 5MB в байтах
            ],
            [
                'key' => 'chat_allowed_file_types',
                'value' => 'jpg,jpeg,png,gif,webp,pdf,doc,docx',
            ],
            [
                'key' => 'chat_typing_timeout',
                'value' => '3000', // миллисекунды
            ],
            [
                'key' => 'chat_message_edit_timeout',
                'value' => '300', // секунд, 5 минут
            ],

            // Настройки темы
            [
                'key' => 'theme',
                'value' => 'default',
            ],
            [
                'key' => 'primary_color',
                'value' => '#3490dc',
            ],
            [
                'key' => 'secondary_color',
                'value' => '#ffed4a',
            ],
            [
                'key' => 'dark_mode_enabled',
                'value' => 'false',
            ],

            // SEO настройки
            [
                'key' => 'google_analytics_id',
                'value' => 'UA-XXXXXXXXX-1',
            ],
            [
                'key' => 'yandex_metrika_id',
                'value' => 'XXXXXXXX',
            ],
            [
                'key' => 'robots_txt',
                'value' => "User-agent: *\nAllow: /\nSitemap: https://example.com/sitemap.xml",
            ],
            [
                'key' => 'meta_author',
                'value' => 'Laravel CMS Team',
            ],

            // Регистрация и аутентификация
            [
                'key' => 'allow_registration',
                'value' => 'true',
            ],
            [
                'key' => 'email_verification_required',
                'value' => 'true',
            ],
            [
                'key' => 'default_user_role',
                'value' => 'user',
            ],

            // Уведомления
            [
                'key' => 'notify_new_user',
                'value' => 'admin@example.com',
            ],
            [
                'key' => 'notify_new_comment',
                'value' => 'admin@example.com',
            ],
            [
                'key' => 'notify_new_message',
                'value' => 'admin@example.com',
            ],

            // Кэширование
            [
                'key' => 'cache_enabled',
                'value' => 'true',
            ],
            [
                'key' => 'cache_ttl',
                'value' => '3600', // 1 час в секундах
            ],

            // Безопасность
            [
                'key' => 'login_attempts',
                'value' => '5',
            ],
            [
                'key' => 'lockout_duration',
                'value' => '60', // минут
            ],
            [
                'key' => 'session_lifetime',
                'value' => '120', // минут
            ],

            // Платежи (если есть)
            [
                'key' => 'currency',
                'value' => 'RUB',
            ],
            [
                'key' => 'tax_rate',
                'value' => '20',
            ],
            [
                'key' => 'payment_methods',
                'value' => 'card,cash,bank_transfer',
            ],

            // API ключи для сторонних сервисов
            [
                'key' => 'recaptcha_site_key',
                'value' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            ],
            [
                'key' => 'recaptcha_secret_key',
                'value' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
            ],
            [
                'key' => 'openai_api_key',
                'value' => 'sk-xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx',
            ],
            [
                'key' => 'map_api_key',
                'value' => 'YOUR_GOOGLE_MAPS_API_KEY',
            ],

            // Maintenance mode
            [
                'key' => 'maintenance_mode',
                'value' => 'false',
            ],
            [
                'key' => 'maintenance_message',
                'value' => 'Сайт на техническом обслуживании. Скоро вернемся!',
            ],
            [
                'key' => 'maintenance_allowed_ips',
                'value' => '127.0.0.1,192.168.1.1',
            ],

            // Cron и автоматизация
            [
                'key' => 'backup_enabled',
                'value' => 'true',
            ],
            [
                'key' => 'backup_schedule',
                'value' => '0 2 * * *', // каждый день в 2 часа ночи
            ],
            [
                'key' => 'cleanup_days',
                'value' => '30', // удалять старые записи через 30 дней
            ],

            // Меню шапки: явный список пунктов (включая блог, без «О нас»)
            [
                'key' => 'menu_items',
                'value' => json_encode([
                    ['title' => 'Главная', 'url' => '/', 'order' => 0, 'children' => []],
                    ['title' => 'Портфолио', 'url' => '/portfolio', 'order' => 1, 'children' => []],
                    ['title' => 'Блог', 'url' => '/blog', 'order' => 2, 'children' => []],
                ]),
            ],
            // Меню (menu_items) не сидим: если настройка пуста, шапка строит
            // вложенное меню из реальных опубликованных страниц.
            [
                'key' => 'footer_widgets',
                'value' => json_encode([
                    'column1' => ['title' => 'О нас', 'content' => 'Текст о компании'],
                    'column2' => ['title' => 'Рубрики', 'type' => 'categories'],
                    'column3' => ['title' => 'Соцсети', 'type' => 'social_links'],
                ]),
            ],
            [
                'key' => 'social_links',
                'value' => json_encode([
                    'telegram' => ['url' => 'https://t.me/chat', 'icon' => 'telegram'],
                    'vk' => ['url' => 'https://vk.com/chat', 'icon' => 'vk'],
                    'youtube' => ['url' => 'https://youtube.com/@chat', 'icon' => 'youtube'],
                    'github' => ['url' => 'https://github.com/chat', 'icon' => 'github'],
                ]),
            ],

            // Локализация
            [
                'key' => 'default_locale',
                'value' => 'ru',
            ],
            [
                'key' => 'available_locales',
                'value' => 'ru,en',
            ],
            [
                'key' => 'date_format',
                'value' => 'd.m.Y',
            ],
            [
                'key' => 'time_format',
                'value' => 'H:i',
            ],
            [
                'key' => 'timezone',
                'value' => 'Europe/Moscow',
            ],
        ];

        foreach ($settings as $setting) {
            // Используем updateOrInsert чтобы избежать дублей при повторном запуске
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                [
                    'value' => $setting['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        // Сбрасываем кэш настроек, чтобы шапка сразу увидела изменения меню
        Cache::forget('site_settings');

        // Дополнительный вывод информации
        $this->command->info('Settings seeded: ' . count($settings) . ' settings inserted/updated');
    }
}
