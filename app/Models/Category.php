<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $guarded = [];

    // relation with table pages
    public function pages() {
        return $this->hasMany(Page::class);
    }
}
