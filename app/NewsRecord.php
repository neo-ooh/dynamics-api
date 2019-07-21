<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class NewsRecord extends Model
{
    protected $table = "news_records";
    protected $fillable = ["cp_id", "subject", "locale", "headline", "date", "media"];
    protected $appends = array('path');

    public function subject() {
        return $this->hasOne('App\NewsSubject');
    }

    public function getPathAttribute() {
        return Storage::disk('public')->url('news/medias/'.$this->media);
    }
}
