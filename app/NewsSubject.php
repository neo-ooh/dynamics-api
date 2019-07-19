<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsSubject extends Model
{
    protected $table = "news_subject";

    public function records() {
        return $this->hasMany('App\NewsRecord');
    }
}
