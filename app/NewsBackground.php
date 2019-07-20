<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class NewsBackground extends Model
{
    protected $appends = array('path');

	public function category() {
	    return $this->belongsTo('App\NewsCategory', 'id', 'category');
    }

    public function getPathAttribute() {
    	return Storage::disk('public')->url('news/backgrounds/'.$this->id);
    }
}
