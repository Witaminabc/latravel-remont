<?php

namespace Teo\Leads\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use SoftDeletes;

    protected $table = 'leads';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'message',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeProcessed($query)
    {
        return $query->where('status', 'processed');
    }
}
