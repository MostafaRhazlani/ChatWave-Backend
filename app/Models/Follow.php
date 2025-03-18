<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    protected $guarded = [];

    // relation of following with table persons
    public function following() {
        return $this->belongsToMany(Person::class, 'follows', 'person_id', 'followed_person_id');
    }

    // relation of following with table persons
    public function followers() {
        return $this->belongsToMany(Person::class, 'follows', 'followed_person_id', 'person_id');
    }
}
