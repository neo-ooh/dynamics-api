<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;
use Illuminate\Support\Facades\Input;

class weatherController extends Controller
{
	private $cities = [
		["CA", "BC", "Vancouver"],
		["CA", "ON", "Mississauga"],
		["CA", "AB", "Calgary"],
		["CA", "AB", "Edmonton"],
		["CA", "NS", "Halifax"],
		["CA", "ON", "Toronto"],
		["CA", "BC", "Victoria"],
		["CA", "QC", "Quebec"],
		["CA", "QC", "Montreal"],
		["CA", "ON", "Ottawa"]
	];

	/**
	 * Gives the national weather
	 * @return Response
	 */
	public function national(): Response {
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
	 * @param String $country CA
	 * @param String $province QC|ON|BC|etc.
	 * @param String $city Toronto|Montreal|etc.
	 * @return Response
	 */
	public function now(String $country, String $province, String $city): Response {
		$link = new MeteoMediaLinkService();

		$locale = Input::get('locale', 'en-CA');

		$now = $link->getNow($locale, $country, $province, $city);
		$longTerm = $link->getNext($locale, $country, $province, $city)["LongTermPeriod"][0];

		$forecast = array_merge($longTerm, $now);

		return new Response($forecast);
	}

	/**
	 * Give the next day weather for the specified location
	 * @param String $country CA
	 * @param String $province QC|ON|BC|etc.
	 * @param String $city Toronto|Montreal|etc.
	 * @return Response
	 */
	public function tomorrow(String $country, String $province, String $city): Response {
		$link = new MeteoMediaLinkService();

		$locale = Input::get('locale', 'en-CA');

		$longTerm = $link->getNext($locale, $country, $province, $city);
		$forecast = $longTerm["LongTermPeriod"][1];
		$forecast["Location"] = $longTerm["Location"];

		return new Response($forecast);
	}

	/**
	 * Give the seven days weather for the specified location
	 * @param String $country CA
	 * @param String $province QC|ON|BC|etc.
	 * @param String $city Toronto|Montreal|etc.
	 * @return Response
	 */
	public function forecast(String $country, String $province, String $city): Response {
		$link = new MeteoMediaLinkService();

		$locale = Input::get('locale', 'en-CA');

		$forecast = $link->getNext($locale, $country, $province, $city);
		array_splice($forecast["LongTermPeriod"], 0, 1);

		return new Response($forecast);
	}
}
