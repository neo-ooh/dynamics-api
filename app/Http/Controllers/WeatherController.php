<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;
use Illuminate\Support\Facades\Input;

class WeatherController extends Controller
{
	private $cities = [
		["CA", "ON", "Toronto"],
		["CA", "ON", "Ottawa"],
		["CA", "QC", "Montreal"],
		["CA", "QC", "Quebec"],
		["CA", "NS", "Halifax"],
		["CA", "BC", "Victoria"],
		["CA", "BC", "Vancouver"],
		["CA", "AB", "Calgary"],
		["CA", "AB", "Edmonton"],
		["CA", "MB", "Winnipeg"],
	];

	/**
	 * Gives the national weather
	 * @return Response
	 */
	public function national(): Response
	{
		$forecasts = [];
		$link = new MeteoMediaLinkService();

		$locale = Input::get('locale', 'en-CA');

		foreach ($this->cities as $city) {
			array_push($forecasts, $link->getNow($locale, ...$city));
		}

		return new Response($forecasts);
	}

	/**
	 * Give the current weather for the specified city
	 * @param String $country  CA
	 * @param String $province QC|ON|BC|etc.
	 * @param String $city     Toronto|Montreal|etc.
	 * @return Response
	 */
	public function now(String $country, String $province, String $city): Response
	{
		$this->sanitizeLocation($country, $province, $city);
		if(!$country || !$province || !$city)
			return new Response(null);

		// Request
		$link = new MeteoMediaLinkService();
		$locale = Input::get('locale', 'en-CA');

		$now = $link->getNow($locale, $country, $province, $city);
		$longTerm = $link->getNext($locale, $country, $province, $city)["LongTermPeriod"][0];

		$forecast = array_merge($longTerm, $now);

		return new Response($forecast);
	}

	/**
	 * Give the next day weather for the specified location
	 * @param String $country  CA
	 * @param String $province QC|ON|BC|etc.
	 * @param String $city     Toronto|Montreal|etc.
	 * @return Response
	 */
	public function tomorrow(String $country, String $province, String $city): Response
	{
		$this->sanitizeLocation($country, $province, $city);
		if(!$country || !$province || !$city)
			return new Response(null);

		$link = new MeteoMediaLinkService();
		$locale = Input::get('locale', 'en-CA');

		$longTerm = $link->getNext($locale, $country, $province, $city);

		if($longTerm == null) return new Response(null);

		$forecast = $longTerm["LongTermPeriod"][1];
		$forecast["Location"] = $longTerm["Location"];

		return new Response($forecast);
	}

	/**
	 * Give the seven days weather for the specified location
	 * @param String $country  CA
	 * @param String $province QC|ON|BC|etc.
	 * @param String $city     Toronto|Montreal|etc.
	 * @return Response
	 */
	public function forecast(String $country, String $province, String $city): Response
	{
		$this->sanitizeLocation($country, $province, $city);
		if(!$country || !$province || !$city)
			return new Response(null);

		$link = new MeteoMediaLinkService();
		$locale = Input::get('locale', 'en-CA');

		$forecast = $link->getNext($locale, $country, $province, $city);

		if($forecast != null)
			array_splice($forecast["LongTermPeriod"], 0, 1);

		return new Response($forecast);
	}


	const PROVINCES = ["NL", "PE", "NS", "NB", "QC", "ON", "MB", "SK", "AB", "BC", "YT", "NT", "NU"];
	const PROVINCES_LNG = [
		"Terre-Neuve-et-Labrador" => "NL",
		"Île-du-Prince-Édouard" => "PE",
		"Nouvelle-Écosse" => "NS",
		"Nouveau-Brunswick" => "NB",
		"Québec" => "QC",
		"Ontario" => "ON",
		"Manitoba" => "MB",
		"Saskatchewan" => "SK",
		"Alberta" => "AB",
		"British Columbia" => "BC",
		"Yukon" => "YT",
		"Northwest Territories" => "NT",
		"Nunavut" => "NU"
	];

	const CITIES = [
		"Ville de Québec" => "Québec"
	];

	private function sanitizeLocation(String &$country, String &$province, String &$city) {
		// Check if the location is valid
		if($country != "CA") $country = null;

		if(!in_array($province, self::PROVINCES)) {
			\Log::info("Invalid province : ".$province);
			if(array_key_exists($province, self::PROVINCES_LNG)) {
				$province = self::PROVINCES_LNG[$province];
			}
		}

		// Make sure the city format is valid
		$city = str_replace(".", "", urldecode($city));
		if(array_key_exists($city, self::CITIES))
			$city = self::CITIES[$city];
	}
}
