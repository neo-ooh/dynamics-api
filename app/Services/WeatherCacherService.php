<?php
/**
 * Created by PhpStorm.
 * User: val
 * Date: 19/07/2018
 * Time: 21:44
 */

namespace App\Services;


use App\Record;

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
	public function get(string $endpoint, string $country, string $province, string $city, string $locale) {
		$records = Record::where([
			"endpoint" => $endpoint,
			"country" => $country,
			"province" => $province,
			"city" => $city,
			"locale" => $locale])
			->get();

		// No record found, stop here
		if(count($records) == 0)
			return null;

		$record = $records[0];
		$lastUpdate = new \DateTime($record->updated_at);

		// record too old, let's remove it
		if($lastUpdate->getTimestamp() + env('RECORD_LIFESPAN', 0) < time()) {
			$record->delete();
			return null;
		}

		// Record OK, let's return it
		return json_decode($record->content, true);
	}

	public function set(string $endpoint, string $country, string $province, string $city, string $locale, string $content) {
		$record = new Record();
		$record->endpoint = $endpoint;
		$record->country = $country;
		$record->province = $province;
		$record->city = $city;
		$record->locale = $locale;
		$record->content = $content;

		$record->save();
	}
}
