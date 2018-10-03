<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeatherLocation extends Model
{
	protected $fillable = array('country', 'province', 'city', 'selection');
    public function backgrounds() {
    	return $this->hasMany('App\WeatherBackground', 'location');
    }
}
