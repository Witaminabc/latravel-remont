# teo/chat

Real-time чат для Laravel + Vue 3:

- **Backend**: `/api/messages*` (виджет на сайте) и `/api/admin/chat/*` (панель «Сообщения»),
  строгая авторизация broadcast-каналов, курсорная пагинация, throttle отправки.
- **Frontend**: Vue-плагин (`@chat`) с Pinia-стором на **Laravel Echo + Reverb**,
  компоненты `ChatLayout`, `ThreadList`, `MessageList`, `Composer`, `StatsCards`.

События вещаются как `.MessageSent` / `.MessagesRead` в `private user.{id}` —
совместимо с прежним виджетом чата на сайте.

## Установка (path-repository)

```json
// composer.json (корень проекта)
"require": { "teo/chat": "*@dev" },
"repositories": [
    { "type": "path", "url": "packages/teo/chat", "options": { "symlink": true } }
]
```

```bash
composer update teo/chat
php artisan package:discover
php artisan chat:install          # публикация config/migrations/views/js
php artisan migrate               # для новых проектов
```

`.env`:

```dotenv
BROADCAST_DRIVER=reverb
QUEUE_CONNECTION=sync             # для прода — очередь (redis/database)
REVERB_APP_KEY=...
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

## Маршруты

Регистрируются автоматически (`src/Http/routes.php`):

| Метод | URI | Назначение |
|---|---|---|
| POST | `/api/broadcasting/auth` | авторизация подписок Echo (middleware `auth:sanctum`) |
| GET | `/api/messages` | история виджета (все сообщения пользователя) |
| POST | `/api/send-message` | отправка виджета (не-админ всегда пишет админу) |
| POST | `/api/messages/mark-read` | прочтение по id (рассылает `.MessagesRead`) |
| GET | `/api/messages/unread-count` | счётчик непрочитанных виджета |
| GET | `/api/admin/chat/me` | текущий пользователь |
| GET | `/api/admin/chat/stats` | карточки статистики |
| GET | `/api/admin/chat/threads` | диалоги: user + last_message + unread |
| GET | `/api/admin/chat/threads/{userId}/messages` | история, `?cursor=&limit=` → `{data, next_cursor}` |
| POST | `/api/admin/chat/threads/{userId}/messages` | отправка (throttle `chat.send`) |
| POST | `/api/admin/chat/threads/{userId}/read` | прочитать входящие |
| POST | `/api/admin/chat/users/{userId}/block` | `{is_blocked: bool}` |
| DELETE | `/api/admin/chat/threads/{userId}` | очистить историю |

Админские маршруты защищены `auth:sanctum` + `Teo\Chat\Http\Middleware\EnsureChatAdmin`
(роль из `config('chat.admin_role')`, JSON-ответы 401/403).

## Каналы вещания

Каналы регистрирует сам пакет (`user.{id}`, `admin`, `chat`) — **не дублируйте их
в `routes/channels.php`**:

- `user.{id}` — подписка разрешена, если `id` совпадает с пользователем
  или пользователь имеет роль `admin` (строгая проверка);
- события: `.MessageSent` (канал получателя + канал админа-отправителя),
  `.MessagesRead` (канал собеседника).

## Конфигурация

`config/chat.php`: `user_model`, `table`, `page_size`, `max_message_length`,
`admin_role`, `throttle.send_per_minute`.

## Frontend (wpadmin)

```js
// vite.config.js
'@chat': path.resolve(__dirname, 'packages/teo/chat/resources/js'),

// main.js
import Chat from '@chat'
app.use(Chat)   // { apiPrefix, authEndpoint, echo } — опционально
```

```vue
<ChatLayout :selected-user-id="userId" @select="id => router.push(...)" />
```

```js
import { useChatStore } from '@chat'
const store = useChatStore()
await store.init()        // me + stats + threads + Echo
await store.openThread(id)
await store.sendMessage('Hello')   // optimistic: pending → sent / failed
```

Требования фронтенда: Vue 3, Pinia, axios, laravel-echo, pusher-js, PrimeVue
(компоненты + ToastService).

## Проверка

```bash
npm run build          # сборка
php artisan route:list  # маршруты
```

## Что сделано (переработка 2026-10)

Полный перепис легаси-чата `teo/chat` в standalone-пакет с интеграцией во вкладку wpadmin.

### Backend
- Path-repository в корневом `composer.json` → `composer update teo/chat` → Junction в
  `vendor/teo/chat`, авто-обнаружение провайдера (`package:discover` ✓).
- Ядро: `ChatServiceProvider` (loadRoutesFrom, mergeConfig, RateLimiter `chat.send`,
  publishes, `chat:install`), `config/chat.php`, `Models/Message` (таблица/модель юзера из конфига).
- События: `MessageSent` (payload **и** имя `.MessageSent` совместимы с прежним виджетом сайта,
  multi-channel: получатель + админ) и новый `MessagesRead` (`.MessagesRead`).
- Маршруты в `src/Http/routes.php`: broadcasting auth + строгие каналы, публичные
  `/api/messages*` (без смены контракта), админ-API `/api/admin/chat/*`
  (`EnsureChatAdmin`, JSON 401/403, throttle, cursor-пагинация, `{data}`-конверт).
- Миграции (messages + колонки users) с guard-проверками от повторного применения;
  `InstallChatCommand` публикует config/migrations.

### Безопасность
- **Удалены обе дыры** `Broadcast::channel(... return true)`:
  - `routes/chat.php` → файл переписан (контрольный хэш сообщений), легаси-каналы удалены;
  - `routes/api.php` → каналы `user.{userId}` и `chat` удалены (вторая дыра перехватывала
    строгую проверку пакета: при совпадении шаблонов `Broadcaster::auth` продолжал перебор
    после `false` и «протаскивал» `true`).
- `routes/channels.php` — только комментарий-редирект; каналы регистрирует пакет.
- Убраны утечки `file`/`line`/`getMessage` из ответов легаси `Api\AdminController` (500 → `{error: ...}`).

### Frontend (пакет `resources/js`)
- Vue-плагин `@chat` по образцу `teo/acf`: `config/http/utils`, Pinia-store на
  **Echo/Reverb** (socket.io удалён): optimistic send (pending → sent/failed + retry),
  keyset-пагинация с восстановлением скролла, дедуп REST↔WS по id, авто-read открытого треда.
- Компоненты: `ChatLayout`, `ThreadList`, `MessageList`, `Composer`, `StatsCards` + `chat.css`.

### wpadmin
- vite-алиас `@chat`, `app.use(Chat)`, роуты `chat` / `chat/:userId`,
  пункт сайдбара «Сообщения» с live-бейджем непрочитанных, `init()` при монтировании,
  `disconnect()` при logout, `views/chat/Index.vue` + ToastService для входящих.

### Валидация (live, HTTP)
| Тест | Результат |
|---|---|
| `php -l` (все файлы), `npm run build` | ✓ / ✓ |
| me / stats / threads / messages+cursor | 200, `{data}`, `next_cursor` |
| send / пустое тело / read / block / delete | 201 / 422 / 200 / 200+404 / 200 |
| не-админ на `/api/admin/chat/*`, без токена | 403, 401 |
| канал `private-user.1` (чужой) / свой / `private-admin` | **403** / 200 / **403** |
| виджет: index, send, mark-read, unread-count | 200 (форматы прежние) |

Осталось вручную: открыть `/admin/chat` в браузере (живой WS через Echo).
Для прода: `QUEUE_CONNECTION=sync` → реальная очередь (redis/database).

### Отступления (осознанные)
1. `client_uuid`-идемпотентность заменена на дедуп по id + фронтенд-guard (миграция не велась).
2. Онлайн-присутствие — по `is_online` (heartbeat/TTL `presence_ttl` оставлены под будущее).

## Отладка: tinker и кавычки (Windows / PowerShell)

Проблема: `php artisan tinker --execute "..."` на Windows — код проходит через
PowerShell → (часто) cmd.exe → PHP. На каждом слое кавычки съедаются или переставляются,
и Psy Shell падает с parse error либо виснет в REPL.

Рабочая схема (проверена на этой машине):

```powershell
# ✅ внешние кавычки PowerShell — ОДИНАРНЫЕ; все строки PHP — тоже одинарные,
#    но внутри PS-литерала каждая ' удваивается ('')
php.exe artisan tinker --execute '$ids = App\Models\User::where(''name'',''admin'')->pluck(''id''); echo ''cnt:'' . count($ids);'

# ✅ та же идея для любых запросов/аргументов Invoke-WebRequest:
Invoke-WebRequest -Uri 'http://127.0.0.1:8000/api/user' -Body '{\"a\":1}' # ← НЕ так: \» превратятся в литеральные \"
Invoke-WebRequest -Uri '...' -Body '{"a":1}' -ContentType 'application/json'  # ✅ JSON — в одинарных PS-кавычках, кавычки JSON обычные
```

Правила, чтобы кавычки не съедали:

1. **Внешние кавычки PowerShell — одинарные** `'...'`: внутри ничего не интерполируется
   (`$ids`, `$_` остаются как текст для PHP, это то, что нужно).
2. **Внутренние строки PHP — через `''`** (удвоение): PHP-код `'message'` пишем в
   PS-источнике как `''message''` — PowerShell склеит обратно в `'message'`.
3. **Никаких двойных кавычек `"` в коде** — cmd.exe их балансирует/поглощает при
   пробросе аргумента, отсюда «съеденные» кавычки. И двойные кавычки PHP ещё и
   интерполируют `$переменные` — для отладки это лишний сюрприз.
4. **Не оставлять пустых `''`**: пустая строка вроде `echo '';` на стыках слоёв
   исчезает целиком и ломает разбор. Пустого текста не бывает — используйте `PHP_EOL`
   или непустые литералы.
5. **Одна строка**: переносы и `%`/`` ` `` в команде не нужны — склеивайте `.` и
   `PHP_EOL`; `%` опасен (команда может раскрыть `%VAR%`), обратные кавычки — это
   синтаксис PowerShell.
6. **Не запускать `tinker file.php`** в фоне без TTY: Psy Shell после выполнения файла
   уходит в REPL и ждёт stdin → процесс висит (пришлось снимать по PID). Если файл
   очень нужен — пишите его **без BOM** (`[IO.File]::WriteAllText($p, $code,
   (New-Object Text.UTF8Encoding $false))`), иначе BOM перед `$кодом` ломает парсер,
   и запускайте с закрытым stdin.
7. `--execute` получает ровно одну строку PHP; читаемость — через `;` и конкатенацию.

Итог того же правила одной строкой:

> Одинарные снаружи, одинарные внутри с удвоением `''`, никаких `"`, никаких пустых `''`.
