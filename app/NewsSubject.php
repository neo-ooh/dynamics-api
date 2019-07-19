<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsSubject extends Model
{
    protected $table = "news_subjects";

    public function records() {
        return $this->hasMany('App\NewsRecord', 'subject');
    }
}
