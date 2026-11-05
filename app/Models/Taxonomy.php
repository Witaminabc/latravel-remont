<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Taxonomy extends Model
{
    protected $fillable = ['name', 'slug', 'type', 'description', 'parent_id'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_taxonomy');
    }

    public function parent()
    {
        return $this->belongsTo(Taxonomy::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Taxonomy::class, 'parent_id');
    }

    public function setSlugAttribute($value)
    {
        $this->attributes['slug'] = Str::slug($value);
    }
}
