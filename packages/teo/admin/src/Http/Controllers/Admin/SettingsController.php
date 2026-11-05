<?php

namespace Teo\Admin\Http\Controllers\Admin;

use Teo\Admin\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Get all settings.
     */
    public function test(){
        return response()->json(['message' => 'Test']);
    }
    public function index()
    {
        $settings = [
            // General settings
            'site_name' => $this->getSetting('site_name', 'My CMS'),
            'site_description' => $this->getSetting('site_description', 'Content Management System'),
            'site_keywords' => $this->getSetting('site_keywords', 'cms, laravel, vue'),
            'site_logo' => $this->getSetting('site_logo', null),
            'site_favicon' => $this->getSetting('site_favicon', null),

            // Contact settings
            'contact_email' => $this->getSetting('contact_email', 'admin@example.com'),
            'contact_phone' => $this->getSetting('contact_phone', '+7 (999) 123-45-67'),
            'contact_address' => $this->getSetting('contact_address', ''),

            // Social links
            'social_telegram' => $this->getSetting('social_telegram', ''),
            'social_max' => $this->getSetting('social_max', ''),
            'social_vk' => $this->getSetting('social_vk', ''),
            'social_whatsapp' => $this->getSetting('social_whatsapp', ''),
            'social_instagram' => $this->getSetting('social_instagram', ''),
            'social_facebook' => $this->getSetting('social_facebook', ''),
            'social_twitter' => $this->getSetting('social_twitter', ''),
            'social_youtube' => $this->getSetting('social_youtube', ''),
            'social_github' => $this->getSetting('social_github', ''),

            // Навигация (шапка сайта): [{ title, url, order }]
            'menu_items' => $this->menuItemsSetting(),

            // System settings
            'items_per_page' => $this->getSetting('items_per_page', 15),
            'maintenance_mode' => $this->getSetting('maintenance_mode', false),
            'allow_registration' => $this->getSetting('allow_registration', true),
            'default_user_role' => $this->getSetting('default_user_role', 'user'),

            // Email settings
            'smtp_host' => $this->getSetting('smtp_host', ''),
            'smtp_port' => $this->getSetting('smtp_port', 587),
            'smtp_encryption' => $this->getSetting('smtp_encryption', 'tls'),
            'smtp_username' => $this->getSetting('smtp_username', ''),
            'smtp_password' => $this->getSetting('smtp_password', ''),

            // SEO settings
            'google_analytics_id' => $this->getSetting('google_analytics_id', ''),
            'yandex_metrika_id' => $this->getSetting('yandex_metrika_id', ''),
            'custom_head_code' => $this->getSetting('custom_head_code', ''),
            'custom_body_code' => $this->getSetting('custom_body_code', ''),
        ];

        // Логотипы соцсетей (необязательные, загружаются отдельным эндпоинтом)
        foreach ($this->socialNetworks() as $network) {
            $settings['social_' . $network . '_logo'] = $this->getSetting('social_' . $network . '_logo', '');
        }

        return response()->json($settings);
    }

    /**
     * Update settings.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // General
            'site_name' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_keywords' => 'nullable|string|max:255',
            'site_logo' => 'nullable|string|max:255',
            'site_favicon' => 'nullable|string|max:255',

            // Contact
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:50',
            'contact_address' => 'nullable|string|max:500',

            // Social
            'social_telegram' => 'nullable|url|max:255',
            'social_max' => 'nullable|url|max:255',
            'social_vk' => 'nullable|url|max:255',
            'social_whatsapp' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_facebook' => 'nullable|url|max:255',
            'social_twitter' => 'nullable|url|max:255',
            'social_youtube' => 'nullable|url|max:255',
            'social_github' => 'nullable|url|max:255',

            // Меню шапки
            'menu_items' => 'nullable|array',
            'menu_items.*.title' => 'nullable|string|max:100',
            'menu_items.*.url' => 'nullable|string|max:255',
            'menu_items.*.order' => 'nullable|integer',
            'menu_items.*.children' => 'nullable|array',
            'menu_items.*.children.*.title' => 'nullable|string|max:100',
            'menu_items.*.children.*.url' => 'nullable|string|max:255',
            'menu_items.*.children.*.order' => 'nullable|integer',

            // System
            'items_per_page' => 'nullable|integer|min:5|max:100',
            'maintenance_mode' => 'nullable|boolean',
            'allow_registration' => 'nullable|boolean',
            'default_user_role' => 'nullable|in:user,editor',

            // Email
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',

            // SEO
            'google_analytics_id' => 'nullable|string|max:50',
            'yandex_metrika_id' => 'nullable|string|max:50',
            'custom_head_code' => 'nullable|string',
            'custom_body_code' => 'nullable|string',
        ]);

        if (array_key_exists('menu_items', $validated)) {
            $validated['menu_items'] = $this->normalizeMenuItems($validated['menu_items']);
        }

        foreach ($validated as $key => $value) {
            $this->setSetting($key, $value);
        }

        // Очищаем кэш (site_settings — данные шапки/SEO из SeoBuilder)
        Cache::forget('app_settings');
        Cache::forget('site_settings');

        return response()->json([
            'message' => 'Settings updated successfully',
            'settings' => $this->index()->getData()
        ]);
    }

    /**
     * Upload logo.
     */
    public function uploadLogo(Request $request)
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,svg|max:2048'
        ]);

        $path = $request->file('logo')->store('settings', 'public');
        $this->setSetting('site_logo', $path);
        Cache::forget('site_settings');

        return response()->json([
            'message' => 'Logo uploaded successfully',
            'path' => Storage::url($path)
        ]);
    }

    /**
     * Загрузка своего логотипа для соцсети (VK / MAX / Telegram / WhatsApp ...).
     */
    public function uploadSocialLogo(Request $request)
    {
        $data = $request->validate([
            'network' => 'required|in:' . implode(',', $this->socialNetworks()),
            'logo' => 'required|image|mimes:jpg,jpeg,png,svg,webp|max:2048',
        ]);

        $path = $request->file('logo')->store('settings/social', 'public');
        $this->setSetting('social_' . $data['network'] . '_logo', $path);
        Cache::forget('site_settings');

        return response()->json([
            'message' => 'Логотип соцсети загружен',
            'path' => $path,
            'url' => Storage::url($path),
        ]);
    }

    /**
     * Сброс логотипа соцсети (шапка вернётся к брендовой SVG-иконке).
     */
    public function removeSocialLogo(Request $request)
    {
        $data = $request->validate([
            'network' => 'required|in:' . implode(',', $this->socialNetworks()),
        ]);

        $key = 'social_' . $data['network'] . '_logo';
        $current = $this->getSetting($key, '');

        if (is_string($current) && $current !== '' && preg_match('#^https?://#i', $current) !== 1) {
            Storage::disk('public')->delete($current);
        }

        $this->setSetting($key, null);
        Cache::forget('site_settings');

        return response()->json(['message' => 'Логотип соцсети сброшен']);
    }

    /**
     * Сети, для которых доступны ссылка и загрузка логотипа.
     *
     * @return array<int, string>
     */
    private function socialNetworks(): array
    {
        return ['telegram', 'max', 'vk', 'whatsapp', 'instagram', 'facebook', 'twitter', 'youtube', 'github'];
    }

    /**
     * Clear cache.
     */
    public function clearCache()
    {
        Cache::flush();

        return response()->json([
            'message' => 'Cache cleared successfully'
        ]);
    }

    /**
     * Пункты меню из настройки (значение может быть массивом или JSON-строкой).
     *
     * @return array<int, array{title: string, url: string, order: int}>
     */
    private function menuItemsSetting(): array
    {
        $value = $this->getSetting('menu_items', []);

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (is_string($decoded)) {
                $decoded = json_decode($decoded, true);
            }

            $value = is_array($decoded) ? $decoded : [];
        }

        return is_array($value) ? $this->normalizeMenuItems($value) : [];
    }

    /**
     * Очистка и переиндексация пунктов меню (order = позиция в списке).
     * Поддерживает один уровень вложенности (children).
     *
     * @param array<int, mixed> $items
     * @return array<int, array{title: string, url: string, order: int, children: array}>
     */
    private function normalizeMenuItems(array $items, int $depth = 0): array
    {
        $clean = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $title = trim((string) ($item['title'] ?? ''));
            $url = trim((string) ($item['url'] ?? ''));

            if ($title === '' || $url === '') {
                continue;
            }

            $children = $depth < 1
                ? $this->normalizeMenuItems((array) ($item['children'] ?? []), $depth + 1)
                : [];

            $clean[] = [
                'title' => $title,
                'url' => $url,
                'order' => count($clean),
                'children' => $children,
            ];
        }

        return $clean;
    }

    /**
     * Get setting value.
     */
    private function getSetting($key, $default = null)
    {
        return Cache::remember('setting_v2_' . $key, 3600, function() use ($key, $default) {
            $setting = \App\Models\Setting::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            // getRawOriginal: старые значения могли быть записаны обычной
            // строкой, и json-cast модели возвращал для них null.
            return $this->decodeSettingValue($setting->getRawOriginal('value'));
        });
    }

    /**
     * JSON-строка из settings → массив/строка; обычная строка без изменений.
     */
    private function decodeSettingValue(mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        $decoded = json_decode($trimmed, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
    }

    /**
     * Set setting value.
     */
    private function setSetting($key, $value)
    {
        \App\Models\Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget('setting_' . $key);
        Cache::forget('setting_v2_' . $key);
    }
}
