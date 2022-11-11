<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $endpoint
 * @property string $country
 * @property string $province
 * @property string $city
 * @property string $locale
 * @property array $content
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class WeatherRecord extends Model
{
    protected $table = "weather_records";

    public $incrementing = false;

    protected $keyType = "string";

	protected $fillable = [
        "endpoint",
        "country",
        "province",
        "city",
        "locale"
    ];

    protected $casts = [
        "content" => "array"
    ];


}
