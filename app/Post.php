<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    const TYPE_INTERNAL = 1;
    const TYPE_PTT = 2;

    protected $guarded = [];
}
