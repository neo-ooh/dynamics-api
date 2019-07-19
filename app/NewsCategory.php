<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsCategory extends Model
{
    protected $table = "news_categories";

    public function subjects() {
        $this->hasMany('App\NewsSubject');
    }
}
