<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsRecord extends Model
{
    protected $table = "news_records";
    protected $fillable = ["cp_id", "subject", "locale", "headline", "date", "media"];
}
