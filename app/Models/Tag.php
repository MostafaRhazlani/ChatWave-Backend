<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    protected $guarded = [];

    // relation with table posts
    public function posts() {
        return $this->belongsToMany(Post::class, 'post_tag', 'post_id', 'tag_id');
    }
}
