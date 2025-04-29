<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = [];

    // relation with table persons
    public function person() {
        return $this->belongsTo(Person::class);
    }

    // relation with table tags
    public function tags() {
        return $this->belongsToMany(Tag::class, 'post_tag', 'post_id', 'tag_id');
    }

    // relation with table likes
    public function likes() {
        return $this->hasMany(Like::class);
    }

    // relation with table comments
    public function comments() {
        return $this->hasMany(Comment::class);
    }

    public function latestThreeComments()
    {
        return $this->hasMany(Comment::class)->latest()->take(3)->with('person');
    }

    public function savedByUsers() {
        return $this->belongsToMany(Person::class, 'saves', 'post_id', 'person_id');
    }
}
