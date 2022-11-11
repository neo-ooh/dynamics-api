<?php
/**
 * Created by PhpStorm.
 * User: val
 * Date: 19/07/2018
 * Time: 21:44
 */

namespace App\Services;


use App\WeatherRecord;
use Carbon\Carbon;

class WeatherCacherService
{
	/**
	 * Tries to retrieve the asked record from the ddb
	 * If the record is retrieven but is too old, it will be removed
	 * @param string $endpoint
	 * @param string $country
	 * @param string $province
	 * @param string $city
	 * @param string $locale
	 * @return WeatherRecord|null
	 */
	public function get(string $endpoint, string $country, string $province, string $city, string $locale)
	{
        return WeatherRecord::where([
			"endpoint" => $endpoint,
			"country" => $country,
			"province" => $province,
			"city" => $city,
			"locale" => $locale
		])->first();
	}

	public function set(
		string $endpoint,
		string $country,
		string $province,
		string $city,
		string $locale,
		array $content
	): WeatherRecord {
        /** @var WeatherRecord $record */
        $record = WeatherRecord::query()->firstOrNew(
	        ['endpoint' => $endpoint,
	        'country' => $country,
	        'province' => $province,
	        'city' => $city,
	        'locale' => $locale]);

	    $record->content = $content;
	    $record->save();

        return $record;
	}

    public function delete(string $endpoint, string $country, string $province, string $city, string $locale) {
        WeatherRecord::query()
            ->where([
                "endpoint" => $endpoint,
                "country" => $country,
                "province" => $province,
                "city" => $city,
                "locale" => $locale
            ])->delete();
    }
}
