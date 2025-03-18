<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = ['sender_id', 'receiver_id', 'content'];

    // relation of sender with table persons
    public function sender() {
        return $this->belongsTo(Person::class, 'sender_id');
    }

    // relation of reveiver with table persons
    public function receiver() {
        return $this->belongsTo(Person::class, 'receiver_id');
    }
}
