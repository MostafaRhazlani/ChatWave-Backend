<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = ['person_id', 'post_id'];
    // relation with table posts
    public function post() {
        return $this->belongsTo(Post::class);
    }

    // relation with table persons
    public function person() {
        return $this->belongsTo(Person::class);
    }
}
