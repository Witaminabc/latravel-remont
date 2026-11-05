<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Teo\Acf\Concerns\HasCustomFields;

class Post extends Model
{
    use HasCustomFields;
    use SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'title', 'slug', 'content', 'excerpt', 'type', 'status',
        'author_id', 'featured_image', 'meta', 'order', 'parent_id', 'published_at'
    ];

    protected $casts = [
        'meta' => 'array',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

protected $appends = ['edit_url', 'view_url'];

    // Отношения
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    public function taxonomies()
    {
        return $this->belongsToMany(Taxonomy::class, 'post_taxonomy')
            ->withPivot('order')
            ->orderBy('pivot_order');
    }

    // Accessors
    public function getEditUrlAttribute()
    {
        $segment = $this->type === 'post' ? 'posts' : 'pages';

        return "/admin/{$segment}/{$this->id}/edit";
    }

    public function getViewUrlAttribute()
    {
        if (empty($this->slug)) {
            return null;
        }

        return $this->slug === 'home' ? '/' : '/' . $this->slug;
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;

        if (empty($this->slug)) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Методы
    public function getMeta($key, $default = null)
    {
        return data_get($this->meta, $key, $default);
    }

    public function setMeta($key, $value)
    {
        $meta = $this->meta ?? [];
        data_set($meta, $key, $value);
        $this->meta = $meta;
        $this->save();
    }
}
