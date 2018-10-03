<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeatherBackground extends Model
{
    public function location() {
    	return $this->belongsTo('App\WeatherLocation', 'location', 'id');
    }
}
