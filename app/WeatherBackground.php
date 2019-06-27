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

    static public function listByParameters($locations, $support, $period) {
        return self::from('weather_backgrounds as a')
            ->select('a.id as id, a.weather as weather')
            ->leftJoin('weather_backgrounds as b', function($join) {
                $join->on('a.weather', '=', 'b.weather');
                $join->on('a.period', '=', 'b.period');
                $join->on('a.support', '=', 'b.support');
                $join->on('a.location', '<', 'b.location');
            })
            ->where('a.support', $support)
            ->where('a.period', $period)
            ->whereIn('a.location', $locations)
            ->whereNull('b.location');
    }
}
