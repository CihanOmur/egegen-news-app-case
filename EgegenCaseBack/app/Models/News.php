<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $guarded = [];

    //factory
    protected static function factory($count)
    {
        return \Database\Factories\NewsFactory::new()->count($count);
    }
}
