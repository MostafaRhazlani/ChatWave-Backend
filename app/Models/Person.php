<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = "persons";

    protected $guarded = [];
    protected $hidden = ['password', 'token'];

    // relation with table post
    public function posts() {
        return $this->hasMany(Post::class);
    }

    // relation with table comments
    public function comments() {
        return $this->hasMany(Comment::class);
    }

    // relation with table likes
    public function likes() {
        return $this->hasMany(Like::class);
    }

    // relation of sent messages with table messages
    public function sentMessage() {
        return $this->hasMany(Message::class, 'sender_id');
    }

    // relation of received messages with table messages
    public function receivedMessage() {
        return $this->hasMany(Message::class, 'receiver_id');
    }

    // relation of following with table persons
    public function following() {
        return $this->belongsToMany(Person::class, 'follows', 'person_id', 'followed_person_id');
    }

    // relation of followers with table persons
    public function followers() {
        return $this->belongsToMany(Person::class, 'follows', 'followed_person_id', 'person_id');
    }

    public function usersBlocked() {
        return $this->belongsToMany(Person::class, 'user_block', 'blocker_id', 'blocked_id');
    }

    public function blockedByUsers() {
        return $this->belongsToMany(Person::class, 'user_block', 'blocked_id', 'blocker_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'receiver_id', 'id')->orderBy('created_at', 'DESC');
    }

    public function savedPosts() {
        return $this->belongsToMany(Post::class, 'saves', 'person_id', 'post_id');
    }
}
