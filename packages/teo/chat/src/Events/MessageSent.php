<?php

namespace Teo\Chat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Teo\Chat\Models\Message;

/**
 * Новое сообщение. Имя события для слушателей — '.MessageSent'
 * (совместимо с существующим виджетом чата на сайте).
 */
class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
        $this->message->loadMissing(['fromUser', 'toUser']);
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        $channels = [];

        // Всегда — в канал получателя
        if ($this->message->to_user) {
            $channels[] = new PrivateChannel('user.' . $this->message->to_user);
        }

        // Если отправитель админ — дублируем в его канал (мульти-вкладки)
        if ($this->message->fromUser?->role === config('chat.admin_role', 'admin')) {
            $channels[] = new PrivateChannel('user.' . $this->message->from_user);
        }

        // Если получатель не указан — отправляем админу
        if ($channels === []) {
            $adminModel = config('chat.user_model');
            $admin = $adminModel::query()
                ->where('role', config('chat.admin_role', 'admin'))
                ->first();
            if ($admin) {
                $channels[] = new PrivateChannel('user.' . $admin->id);
            }
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'message' => $this->message->message,
            'is_read' => (bool) $this->message->is_read,
            'created_at' => $this->message->created_at?->toISOString(),
            'from_user' => $this->message->fromUser ? [
                'id' => $this->message->fromUser->id,
                'name' => $this->message->fromUser->name,
                'email' => $this->message->fromUser->email,
            ] : null,
            'to_user' => $this->message->toUser ? [
                'id' => $this->message->toUser->id,
                'name' => $this->message->toUser->name,
                'email' => $this->message->toUser->email,
            ] : null,
        ];
    }
}