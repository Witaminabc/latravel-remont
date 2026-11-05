<?php

namespace Teo\Chat\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Сообщение чата. Работает с таблицей из config('chat.table')
 * и связями на модель пользователей из config('chat.user_model').
 */
class Message extends Model
{
    protected $table = 'messages';

    protected $fillable = [
        'from_user',
        'to_user',
        'user_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function getTable()
    {
        return config('chat.table', $this->table);
    }

    public function fromUser(): BelongsTo
    {
        return $this->belongsTo(config('chat.user_model'), 'from_user');
    }

    public function toUser(): BelongsTo
    {
        return $this->belongsTo(config('chat.user_model'), 'to_user');
    }

    /**
     * Legacy-связь (старая схема: колонка user_id = автор).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('chat.user_model'), 'user_id');
    }
}