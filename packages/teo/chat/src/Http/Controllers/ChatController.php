<?php

namespace Teo\Chat\Http\Controllers;

use Illuminate\Http\Request;
use Teo\Chat\Events\MessageSent;
use Teo\Chat\Events\MessagesRead;
use Teo\Chat\Models\Message;

/**
 * Публичное API виджета чата на сайте.
 * Пути сохранены от прежней реализации для совместимости:
 *   GET  /api/messages
 *   POST /api/send-message
 *   POST /api/messages/mark-read
 *   GET  /api/messages/unread-count
 */
class ChatController
{
    public function index(Request $request)
    {
        $user = $request->user();

        $messages = Message::with(['fromUser', 'toUser'])
            ->where(function ($q) use ($user) {
                $q->where('from_user', $user->id)
                    ->orWhere('to_user', $user->id);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $userModel = config('chat.user_model');

        $request->validate([
            'message' => ['required', 'string', 'max:' . (int) config('chat.max_message_length', 1000)],
            'to_user' => ['nullable', 'integer', 'exists:' . (new $userModel())->getTable() . ',id'],
        ]);

        $user = $request->user();

        if ($user->is_blocked) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $toUserId = $request->input('to_user');

        // Не-админ всегда пишет админу
        if (($user->role ?? null) !== config('chat.admin_role', 'admin')) {
            $admin = $userModel::query()
                ->where('role', config('chat.admin_role', 'admin'))
                ->first();

            if (! $admin) {
                return response()->json(['error' => 'Admin not found'], 404);
            }

            $toUserId = $admin->id;
        }

        if ((int) $toUserId === (int) $user->id) {
            return response()->json(['error' => 'Cannot send message to yourself'], 400);
        }

        $message = Message::create([
            'from_user' => $user->id,
            'user_id' => $user->id,
            'to_user' => $toUserId,
            'message' => $request->input('message'),
            'is_read' => false,
        ]);

        $message->load(['fromUser', 'toUser']);

        broadcast(new MessageSent($message));

        return response()->json($message);
    }

    public function markAsRead(Request $request)
    {
        $table = config('chat.table', 'messages');

        $request->validate([
            'message_ids' => ['required', 'array'],
            'message_ids.*' => ['integer', 'exists:' . $table . ',id'],
        ]);

        $user = $request->user();
        $ids = $request->input('message_ids');

        $updated = Message::whereIn('id', $ids)
            ->where('to_user', $user->id)
            ->update(['is_read' => true]);

        // Сообщаем отправителям, что их сообщения прочитаны (галочки)
        if ($updated > 0) {
            $peers = Message::whereIn('id', $ids)
                ->where('to_user', $user->id)
                ->whereNotNull('from_user')
                ->distinct()
                ->pluck('from_user');

            foreach ($peers as $peerId) {
                broadcast(new MessagesRead(
                    readerId: (int) $user->id,
                    peerId: (int) $peerId,
                ));
            }
        }

        return response()->json(['success' => true, 'updated' => $updated]);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();

        $count = Message::where('to_user', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json(['unread_count' => $count]);
    }
}