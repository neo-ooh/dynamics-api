<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

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
        return self::hydrate(DB::table('weather_backgrounds as a')
            ->select('a.id as id',
                'a.weather as weather',
                'a.period as period',
                'a.support as support',
                'a.created_at as created_at',
                'a.updated_at as updated_at',
                'a.location as location')
            ->leftJoin('weather_backgrounds as b', function($join) {
                $join->on('a.weather', '=', 'b.weather');
                $join->on('a.period', '=', 'b.period');
                $join->on('a.support', '=', 'b.support');
                $join->on('a.location', '<', 'b.location');
            })
            ->where('a.support', $support)
            ->where('a.period', $period)
            ->whereIn('a.location', $locations)
            ->whereNull('b.location')
            ->get()->toArray())->with('location');
    }
}
