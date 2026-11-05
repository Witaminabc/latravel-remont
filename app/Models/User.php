<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_online',
        'last_seen',
        'is_blocked'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_online' => 'boolean',
        'is_blocked' => 'boolean',
    ];
    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'from_user');
    }

    public function receivedMessages()
    {
        return $this->hasMany(Message::class, 'to_user');
    }

}
