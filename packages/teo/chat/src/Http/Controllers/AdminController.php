<?php

namespace Teo\Chat\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Teo\Chat\Events\MessageSent;
use Teo\Chat\Events\MessagesRead;
use Teo\Chat\Models\Message;

/**
 * API панели «Сообщения» (wpadmin).
 *
 * Ответы — в конверте {data: ...}; история сообщений дополнительно
 * возвращает next_cursor для курсорной пагинации.
 */
class AdminController
{
    /** Текущий пользователь (id, name, email, role). */
    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? null,
            ],
        ]);
    }

    /** Сводка для карточек статистики. */
    public function stats(Request $request)
    {
        $userModel = config('chat.user_model');
        $meId = (int) $request->user()->id;

        return response()->json([
            'data' => [
                'total_users' => $userModel::query()->count(),
                'online_users' => $userModel::query()->where('is_online', true)->count(),
                'blocked_users' => $userModel::query()->where('is_blocked', true)->count(),
                'unread_messages' => Message::query()
                    ->where('to_user', $meId)
                    ->where('is_read', false)
                    ->count(),
                'total_messages' => Message::query()->count(),
                'threads' => count($this->lastMessageRows($meId)),
            ],
        ]);
    }

    /** Список диалогов: собеседник + последнее сообщение + непрочитанные. */
    public function threads(Request $request)
    {
        $userModel = config('chat.user_model');
        $meId = (int) $request->user()->id;

        $rows = $this->lastMessageRows($meId);

        if ($rows === []) {
            return response()->json(['data' => []]);
        }

        $lastIds = array_map(fn ($row) => (int) $row->last_id, $rows);
        $peerIds = array_values(array_unique(array_map(fn ($row) => (int) $row->peer, $rows)));

        $lastMessages = Message::with(['fromUser', 'toUser'])
            ->whereIn('id', $lastIds)
            ->get()
            ->keyBy('id');

        $users = $userModel::query()->whereIn('id', $peerIds)->get()->keyBy('id');

        $unreadRows = Message::query()
            ->where('to_user', $meId)
            ->where('is_read', false)
            ->whereNotNull('from_user')
            ->groupBy('from_user')
            ->select('from_user', DB::raw('COUNT(*) as cnt'))
            ->get()
            ->keyBy('from_user');

        $threads = [];

        foreach ($rows as $row) {
            $message = $lastMessages->get((int) $row->last_id);
            $peer = $users->get((int) $row->peer);

            if (! $message || ! $peer) {
                continue;
            }

            $threads[] = [
                'user' => $this->mapUser($peer),
                'last_message' => $this->mapMessage($message),
                'unread' => (int) ($unreadRows->get((int) $row->peer)?->cnt ?? 0),
            ];
        }

        // Сортировка: самые свежие диалоги сверху (ISO-строки сравнимы как строки)
        usort(
            $threads,
            fn ($a, $b) => strcmp($b['last_message']['created_at'] ?? '', $a['last_message']['created_at'] ?? '')
        );

        return response()->json(['data' => $threads]);
    }

    /**
     * История диалога с пользователем (курсорная пагинация).
     * GET /api/admin/chat/threads/{userId}/messages?cursor=&limit=
     */
    public function messages(Request $request, int $userId)
    {
        $peer = $this->findUser($userId);
        if (! $peer) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $meId = (int) $request->user()->id;
        $limit = (int) $request->query('limit', (int) config('chat.page_size', 50));
        $limit = min(max($limit, 1), (int) config('chat.max_page_size', 100));
        $cursor = (int) $request->query('cursor', 0);

        $query = Message::with(['fromUser', 'toUser'])
            ->where(fn ($q) => $q->where('from_user', $meId)->orWhere('to_user', $meId))
            ->where(fn ($q) => $q->where('from_user', $userId)->orWhere('to_user', $userId));

        if ($cursor > 0) {
            $query->where('id', '<', $cursor);
        }

        $items = $query->orderByDesc('id')->limit($limit + 1)->get();

        $hasMore = $items->count() > $limit;
        if ($hasMore) {
            $items = $items->slice(0, $limit);
        }

        $nextCursor = $hasMore && $items->isNotEmpty() ? (int) $items->last()->id : null;

        return response()->json([
            'data' => $items->sortBy('id')->values()
                ->map(fn (Message $message) => $this->mapMessage($message))
                ->all(),
            'next_cursor' => $nextCursor,
        ]);
    }

    /** Отправка сообщения от админа. POST /api/admin/chat/threads/{userId}/messages */
    public function send(Request $request, int $userId)
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:' . (int) config('chat.max_message_length', 1000)],
        ]);

        if (! $this->findUser($userId)) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $me = $request->user();

        $message = Message::create([
            'from_user' => $me->id,
            'user_id' => $me->id,
            'to_user' => $userId,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        $message->load(['fromUser', 'toUser']);

        broadcast(new MessageSent($message));

        return response()->json(['data' => ['message' => $this->mapMessage($message)]], 201);
    }

    /** Прочитать входящие от пользователя. POST /api/admin/chat/threads/{userId}/read */
    public function read(Request $request, int $userId)
    {
        $meId = (int) $request->user()->id;

        $updated = Message::query()
            ->where('from_user', $userId)
            ->where('to_user', $meId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        if ($updated > 0) {
            broadcast(new MessagesRead(readerId: $meId, peerId: $userId));
        }

        return response()->json(['data' => ['updated' => $updated]]);
    }

    /** Блокировка/разблокировка. POST /api/admin/chat/users/{userId}/block */
    public function block(Request $request, int $userId)
    {
        $validated = $request->validate([
            'is_blocked' => ['required', 'boolean'],
        ]);

        $peer = $this->findUser($userId);
        if (! $peer) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $peer->update(['is_blocked' => $validated['is_blocked']]);

        return response()->json(['data' => ['is_blocked' => (bool) $peer->is_blocked]]);
    }

    /** Очистить историю диалога. DELETE /api/admin/chat/threads/{userId} */
    public function clear(Request $request, int $userId)
    {
        $meId = (int) $request->user()->id;

        $deleted = Message::query()
            ->where(fn ($q) => $q->where('from_user', $meId)->orWhere('to_user', $meId))
            ->where(fn ($q) => $q->where('from_user', $userId)->orWhere('to_user', $userId))
            ->delete();

        return response()->json(['data' => ['deleted' => $deleted]]);
    }

    /**
     * Последнее сообщение каждого диалога текущего пользователя.
     *
     * @return array<int, object{last_id: int|string, peer: int|string}>
     */
    private function lastMessageRows(int $meId): array
    {
        $table = config('chat.table', 'messages');

        return DB::select(
            "SELECT MAX(id) AS last_id,
                    CASE WHEN from_user = ? THEN to_user ELSE from_user END AS peer
             FROM {$table}
             WHERE (from_user = ? OR to_user = ?)
               AND from_user IS NOT NULL
               AND to_user IS NOT NULL
             GROUP BY peer",
            [$meId, $meId, $meId]
        );
    }

    private function findUser(int $userId)
    {
        return config('chat.user_model')::query()->find($userId);
    }

    private function mapMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'message' => $message->message,
            'is_read' => (bool) $message->is_read,
            'created_at' => $message->created_at?->toISOString(),
            'from_user' => $message->fromUser
                ? $this->mapUserBrief($message->fromUser)
                : null,
            'to_user' => $message->toUser
                ? $this->mapUserBrief($message->toUser)
                : null,
        ];
    }

    private function mapUserBrief($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ];
    }

    private function mapUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role ?? null,
            'is_online' => (bool) ($user->is_online ?? false),
            'is_blocked' => (bool) ($user->is_blocked ?? false),
            'last_seen' => $user->last_seen ?? null,
        ];
    }
}