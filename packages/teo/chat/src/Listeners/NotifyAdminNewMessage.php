<?php

namespace Teo\Chat\Listeners;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Teo\Chat\Events\MessageSent;
use Teo\Chat\Notifications\NewChatMessage;

/**
 * Уведомление о новом сообщении чата на email (config('chat.notify')).
 *
 * Логика:
 *  - письмо отправляется, когда автор сообщения НЕ админ (посетитель);
 *    ответы админа по умолчанию игнорируются, чтобы не спамить себе;
 *  - «скользящее окно тишины» (coalescing): письмо уходит ТОЛЬКО когда
 *    посетитель выходит на связь после паузы (первое сообщение или пауза
 *    не меньше chat.notify.idle_window). Пока идёт переписка чаще, чем окно —
 *    письма не дублируются, окно продлевается. Прочтение админом НЕ влияет:
 *    разблокирует только пауза >= idle_window секунд;
 *  - при CHAT_NOTIFY_ENABLED=false или пустом адресе — ничего не делает;
 *  - сбой SMTP/кэша логируется и НЕ ломает отправку сообщения в чат
 *    (важно при QUEUE_CONNECTION=sync).
 */
class NotifyAdminNewMessage
{
    public function handle(MessageSent $event): void
    {
        $config = (array) config('chat.notify', []);

        if (empty($config['enabled'])) {
            return;
        }

        $message = $event->message;

        if (! empty($config['only_from_non_admin'])) {
            $authorRole = $message->fromUser?->role ?? null;
            if ($authorRole !== null && $authorRole === config('chat.admin_role', 'admin')) {
                return;
            }
        }

        $email = $config['email'] ?? null;
        if (empty($email)) {
            return;
        }

        // Окно тишины (сек) между письмами по одному диалогу. Кэшируем по
        // автору (from_user), т.е. по «треду» переписки. Защита от некорректных
        // значений (<=0 трактуем как «шлём каждое»).
        $window = (int) ($config['idle_window'] ?? 60);
        $fromUserId = $message->from_user ?? 0;
        $key = 'chat.notify.window:'.$fromUserId;

        try {
            if ($window > 0) {
                // add — атомарно (SET NX): ключа нет → это всплеск после тишины
                // (первое сообщение или пауза истекла) → отправляем письмо.
                if (! Cache::add($key, true, $window)) {
                    // Ключ есть — активная сессия в пределах окна. Продлеваем
                    // окно (скользящее), но НЕ отправляем письмо. Прочтение
                    // админом здесь не участвует — разблокирует только пауза.
                    Cache::put($key, true, $window);

                    return;
                }
            }

            Notification::route('mail', $email)->notify(new NewChatMessage($message));
        } catch (\Throwable $e) {
            Log::error('Chat: не удалось отправить email-уведомление о новом сообщении', [
                'message_id' => $message->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

