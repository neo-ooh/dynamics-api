<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class WeatherBackground extends Model
{
	public $with = ['location'];
	protected $appends = array('path');

    public function location() {
    	return $this->belongsTo('App\WeatherLocation', 'location', 'id');
    }

    public function getPathAttribute() {
    	return Storage::url('backgrounds/'.$this->id);
    }
}
