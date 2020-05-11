<?php
/**
 * Created by PhpStorm.
 * User: val
 * Date: 19/07/2018
 * Time: 21:44
 */

namespace App\Services;


use App\WeatherRecord;

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
	 * @return The record or NULL
	 */
	public function get(string $endpoint, string $country, string $province, string $city, string $locale)
	{
		$records = WeatherRecord::where([
			"endpoint" => $endpoint,
			"country" => $country,
			"province" => $province,
			"city" => $city,
			"locale" => $locale
		])
			->get();

		// No record found, stop here
		if (count($records) == 0) {
//			\Log::info("Requested record does not exist in the DDB");
			return null;
		}

		$record = $records[0];
		$lastUpdate = new \DateTime($record->updated_at);

		// record too old, let's remove it
//		\Log::info("WeatherRecord age = ".(time() - $lastUpdate->getTimestamp()));
		if ($lastUpdate->getTimestamp() + config('app.api.lifespan') < time()) {
//			\Log::info("WeatherRecord found in DDB but was too old.");
			$record->delete();
			return null;
		}

		// WeatherRecord OK, let's return it
		return json_decode($record->content, true);
	}

	public function set(
		string $endpoint,
		string $country,
		string $province,
		string $city,
		string $locale,
		string $content
	) {
	    $record = WeatherRecord::updateOrCreate(
	        ['endpoint' => $endpoint,
	        'country' => $country,
	        'province' => $province,
	        'city' => $city,
	        'locale' => $locale]
        , ['content' => $content]);
	}
}
