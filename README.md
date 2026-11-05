# CMS — Laravel + Inertia + Vue (модуль `teo/pages`)

CMS на Laravel: админка и публичный сайт на Vue 3. Основной модуль — **`packages/teo/pages`**: страницы, шаблоны с блоками, SEO, превью, sitemap.

## Стек

- **Backend:** Laravel, Inertia (серверная часть), Sanctum (admin API), path-репо Composer для `packages/teo/*`
- **Frontend:** Vue 3, `@inertiajs/vue3` **v3.8.0** (публичный сайт), vue-router + pinia (админка/wpadmin), Vite
- **Соседние модули:** `teo/acf` (поля), `teo/chat`

## Структура `packages/teo/pages`

```
packages/teo/pages/
├── config/pages.php                  # префикс/middleware админ-API, preview_link_ttl, sitemap_cache_ttl
├── resources/js/index.js             # клиентский SEO-синк (meta, OG, JSON-LD) для Inertia-переходов
└── src/
    ├── PagesServiceProvider.php
    ├── Http/routes.php               # fallback-catch-all, /preview/{id} (signed), /sitemap.xml, admin API
    ├── Http/Controllers/
    │   ├── PageTemplateController.php    # публичный рендер + SEO в пропсах
    │   ├── SitemapController.php         # GET /sitemap.xml (published, кэш)
    │   ├── Admin/PreviewController.php   # POST preview (iframe), POST preview-link (signed)
    │   └── Admin/TemplateController.php  # список шаблонов для админки
    └── Support/
        ├── TemplateRegistry.php      # реестр шаблонов (группы, блоки, meta)
        ├── SeoManager.php            # SEO в <head> на сервере
        ├── SeoBuilder.php            # meta + JSON-LD (WebSite, Organization, BreadcrumbList)
        └── HtmlSanitizer.php         # allowlist-очистка HTML (script/style/on*/javascript:, один <h1>)
```

Ключевые файлы в самом приложении:

- `config/inertia.php` — **обязателен** (см. «Исправленные ошибки», №1)
- `resources/views/inertia.blade.php` — корневой шаблон публички (meta, OG, JSON-LD, `@inertia`)
- `resources/js/public/app.js` — Inertia-entry публичного сайта
- `resources/js/templates/` — шаблоны страниц (`Home`, `Default`, `FieldsTest`) и блоки (`blocks/Hero`, `blocks/BannerSlider`, `blocks/Features`, `blocks/Tariffs`, `blocks/PriceTable`…)
- `resources/js/wpadmin/views/pages/Form.vue` — админская форма (блок-панель, превью)
- `public/robots.txt`

## План работ

| # | Фаза | Статус |
|---|------|--------|
| 1 | Ядро: конфиг, `TemplateRegistry`, `PageTemplatesProvider`, `Route::fallback`, admin API `page-templates` | ✅ |
| 2 | Админка страниц (Index/Form во `wpadmin`) | ✅ |
| 3 | Шаблоны и блоки, тумблер `blocks_enabled` | ✅ |
| 4 | SEO-менеджер: `SeoManager` → `<head>`, OG/Twitter, canonical | ✅ |
| 5 | Поля (ACF, модуль `teo/acf`) | ✅ |
| 6 | Превью: рендер черновика + подписанная ссылка | ✅ |
| 7 | Форма: блок-панель, превью в Dialog+iframe, гейтинг, dev-warnings | ✅ |
| 8 | SEO hardening: `HtmlSanitizer`, sitemap, robots, JSON-LD, абсолютные URL | ✅ |
| 9 | Hotfix: Inertia v3 — пустая публичная страница | ✅ |
| 10 | Динамический `robots.txt` (route вместо статики, prod-URL) | ⬜ TODO |
| 11 | Инвалидация кэша sitemap при сохранении (сейчас только TTL) | ⬜ TODO |
| 12 | Санитизация ACF-wysiwyg полей | ⬜ TODO |

## Что сделано

### Фаза 7 — форма редактирования (frontend)

- Блок-панель в `Form.vue`: аккордеон групп шаблонов из `page-templates`, тумблер `blocks_enabled.*` в meta, dev-warnings на незарегистрированные блоки.
- Превью: `Dialog` + `iframe` через `POST /api/admin/pages/preview` (замена legacy `window.open`); подписанная ссылка на черновик `POST /api/admin/pages/preview-link` (TTL из `pages.preview_link_ttl`, рендер любого статуса с `robots=noindex` для непубликованных).
- Гейтинг секций в `Home.vue` / `FieldsTest.vue` по `meta.blocks_enabled.*`.
- Валидация: T1 401 без токена, T2 noindex консистентен в `<head>` и пропсах, T3/T5 подписанная ссылка 200 / подделка 403, T6 roundtrip блоков, T7 `/` и `/admin` → 200.

### Фаза 8 — SEO hardening

- `HtmlSanitizer` (allowlist тегов/атрибутов, срез `script`/`style`/`on*`/`javascript:`, один `<h1>`) вместо `strip_tags` в `page.content` и `generateDescription()` — 9/9 unit-тестов.
- `GET /sitemap.xml` (`SitemapController`): только `published`, `/` первым, кэш по `sitemap_cache_ttl`; строка `Sitemap` в `public/robots.txt`.
- JSON-LD: `SeoBuilder::jsonLd()` — `WebSite`, `Organization`, `BreadcrumbList` (цепочка `parent_id` с кэшем); вывод в `inertia.blade.php`, клиентская синхронизация `applyJsonLd` в `packages/teo/pages/resources/js/index.js`.
- Абсолютные URL картинок (`absoluteUrl()`) для OG/Twitter.
- Валидация: sitemap 200/xml, 2 схемы JSON-LD на `/`, `php -l` OK, `npm run build` зелёный, лог чистый.

### Фаза 9 — hotfix Inertia v3 (10.06.2026)

- Создан `config/inertia.php` с `use_script_element_for_initial_page => true` (env `INERTIA_USE_SCRIPT_ELEMENT_FOR_INITIAL_PAGE`).
- Headless-браузер (Edge `--dump-dom` + `--enable-logging=stderr`): `/` и `/home` отрендерены (`<h1>Добро пожаловать</h1>`), console без ошибок; `/`, `/home`, `/test-fields` → 200 и `<script data-page="app" type="application/json">` в HTML; title, 2 схемы JSON-LD и `index, follow` на месте.

## Исправленные ошибки

1. **Пустая публичная страница при HTTP 200 (критично, Inertia v3).**
   Симптом: `/` и `/home` отдавали корректный HTML с `data-page`, но `<div id="app">` оставался пустым.
   Причина: `@inertiajs/vue3` v3.8.0 читает начальную страницу **только** из `<script data-page="app" type="application/json">`, а директива `@inertia` (inertia-laravel v2.0.12) рендерит этот тег лишь при `config('inertia.use_script_element_for_initial_page') = true`. В проекте отсутствовал `config/inertia.php` → старый `<div data-page>` → `Yn('app')` = `null` → `TypeError: Cannot read properties of null (reading 'component')` → Vue не монтировался.
   Фикс: `config/inertia.php` с флагом `true`.
   Диагностика: headless Edge, `--dump-dom` + `--enable-logging=stderr`.

2. **Десинхрон noindex между `<head>` и пропсами.**
   Симптом: мета `robots` в HTML и `props.seo` расходились.
   Причина: `seoOverrides` применялись **после** `SeoManager::put` — head собирался со старыми значениями.
   Фикс: перенос `seoOverrides` внутрь `renderPage` **до** `SeoManager::put` (`PageTemplateController`).

3. **Текст `<script>` утекал в meta description.**
   Симптом: в автодескрипшне появлялся мусор (`function(){…}` и т.п.).
   Причина: `strip_tags()` вырезает теги, но сохраняет **внутренний текст** `script`/`style`.
   Фикс: `Support/HtmlSanitizer.php` (allowlist) вместо `strip_tags` в `generateDescription()` и для `page.content`.

4. **Превью через `window.open`.**
   Симптом: ломалось popup-блокировщиками, гонка сессии/авторизации.
   Фикс: превью в `Dialog` + `iframe` через `POST /api/admin/pages/preview` и подписанная ссылка `/preview/{id}` (`ValidateSignature`, TTL `pages.preview_link_ttl`).

5. **Catch-all перехватывал служебные маршруты.**
   Симптом: вариант `Route::get('/{slug}')` мог перехватывать `/admin`, `/api/*`.
   Фикс: `Route::fallback()` — матчится последним среди всех роутов приложения; многосегментные пути → 404.

6. **Относительные URL картинок в OG/Twitter.**
   Симптом: парсеры соцсетей получали `/uploads/...` без домена.
   Фикс: `absoluteUrl()` в `SeoBuilder`.

## Известные ограничения

- `public/robots.txt` статический, Sitemap-URL указан как `http://localhost/sitemap.xml` — для продакшена нужен динамический route (фаза 10).
- Кэш sitemap инвалидируется только по TTL (фаза 11).
- ACF-wysiwyg поля пока не санитизируются — только `page.content` и автодескрипшн (фаза 12).
- **Не репаблишить** дефолтный vendor-конфиг inertia со значением `use_script_element_for_initial_page => false` — публичные страницы снова станут пустыми.

## Запуск и проверка

```bash
composer install            # path-репо packages/teo/*, внешние зависимости — Packagist
npm install
npm run dev                 # Vite dev-сервер: 127.0.0.1:5173
npm run build               # прод-сборка в public/build
php artisan serve           # http://127.0.0.1:8000
```

Быстрая проверка: `/`, `/home`, `/test-fields` → 200 и рендер контента; `/sitemap.xml` → XML; `GET /preview/{id}` с валидной подписью → 200, с подделанной → 403; админский API без токена → 401.

---

Основано на [Laravel](https://laravel.com) ([лицензия MIT](https://opensource.org/licenses/MIT)).


