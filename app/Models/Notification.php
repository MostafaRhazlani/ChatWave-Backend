<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'receiver_id', 'sender_id', 'type', 'content', 'is_read'
    ];

    public function receiver()
    {
        return $this->belongsTo(Person::class, 'receiver_id');
    }

    public function sender()
    {
        return $this->belongsTo(Person::class, 'sender_id');
    }
}
