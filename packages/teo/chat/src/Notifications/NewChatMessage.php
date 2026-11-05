<?php

namespace Teo\Chat\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Teo\Chat\Models\Message;

/**
 * Email-уведомление о новом сообщении чата от посетителя.
 * Отправляется через Listener NotifyAdminNewMessage на адрес из
 * config('chat.notify.email'). Реализует ShouldQueue — при включённой
 * очереди не блокирует ответ /api/send-message.
 */
class NewChatMessage extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Message $message)
    {
    }

    /**
     * @return array<int, string>
     */
    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = $this->message;
        $message->loadMissing(['fromUser', 'toUser']);

        $author = $message->fromUser;
        $authorName = $author?->name ?: 'Гость';
        $authorEmail = $author?->email ?: '—';
        $appName = config('app.name', 'Сайт');
        $replyUrl = rtrim((string) config('app.url'), '/') . '/admin/chat';
        $sentAt = optional($message->created_at)->format('d.m.Y H:i') ?: now()->format('d.m.Y H:i');

        return (new MailMessage())
            ->subject('Новое сообщение в чате от ' . $authorName)
            ->greeting('Новое сообщение из чата на сайте «' . $appName . '»')
            ->line('**Отправитель:** ' . $authorName . ' (' . $authorEmail . ')')
            ->line('**Дата:** ' . $sentAt)
            ->line('**Сообщение:**')
            ->line($message->message)
            ->action('Ответить в панели', $replyUrl)
            ->line('Письмо отправлено автоматически, отвечать на него не нужно.');
    }
}
