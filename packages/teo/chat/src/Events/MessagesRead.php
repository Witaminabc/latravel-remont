<?php

namespace Teo\Chat\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Сообщения собеседника прочитаны. Рассылается в канал user.{peerId},
 * чтобы отправитель увидел галочки «прочитано».
 */
class MessagesRead implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $readerId,
        public int $peerId,
    ) {
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.' . $this->peerId)];
    }

    public function broadcastAs(): string
    {
        return 'MessagesRead';
    }

    public function broadcastWith(): array
    {
        return [
            'reader_id' => $this->readerId,
            'read_at' => now()->toISOString(),
        ];
    }
}