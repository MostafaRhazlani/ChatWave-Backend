<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = "persons";

    protected $guarded = [];
    protected $hidden = ['password', 'token'];

    public function posts() {
        return $this->hasMany(Post::class);
    }
}
