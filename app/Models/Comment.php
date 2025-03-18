<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $guarded = [];

    // relation with table persons
    public function person() {
        return $this->belongsTo(Person::class);
    }

    public function post() {
        return $this->belongsTo(Post::class);
    }
}
