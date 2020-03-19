<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class WeatherRecord extends Model
{
    protected $table = "weather_records";
	public $incrementing = false;
	protected $keyType = "string";

}
