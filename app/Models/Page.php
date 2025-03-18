<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $guarded = [];

    // relation with table categories
    public function category() {
        return $this->belongsTo(Category::class);
    }

    // relation with table persons
    public function owner() {
        return $this->belongsTo(Person::class);
    }

    // relation with table posts
    public function posts() {
        return $this->hasMany(Post::class);
    }

}
