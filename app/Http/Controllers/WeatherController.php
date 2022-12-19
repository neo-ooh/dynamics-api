<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;
use Log;

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

		$locale = Request('locale', 'en-CA');

		foreach ($this->cities as $city) {
            $forecasts[] = $link->getNow($locale, ...$city)?->content ?? [];
		}

		return new Response($forecasts);
	}

    /**
     * Give the current weather for the specified city
     *
     * @param Request $request
     * @param string  $country
     * @param string  $province
     * @param string  $city
     * @return Response
     */
	public function now(Request $request, string $country, string $province, string $city): Response
	{
		$this->sanitizeLocation($country, $province, $city);
		if(!$country || !$province || !$city) {
            return new Response(null);
        }

		// Request
		$link = new MeteoMediaLinkService();
		$locale = $request->input('locale', 'en-CA');

		$now = $link->getNow($locale, $country, $province, $city);
		$longTerm = $link->getNext($locale, $country, $province, $city);

        if(!$now || !$longTerm) {
            return new Response(["could not contact Weather API"], 502);
        }

		$forecast = array_merge($now->content, $longTerm->content["LongTermPeriod"][0]);

		return new Response($forecast, 200);
	}

    /**
     * Give the next day weather for the specified location
     *
     * @param Request $request The request
     * @param string  $country
     * @param string  $province
     * @param string  $city
     * @return Response
     */
	public function tomorrow(Request $request, string $country, string $province, string $city): Response
	{
        $this->sanitizeLocation($country, $province, $city);
		if(!$country || !$province || !$city) {
            return new Response(null);
        }

		$link = new MeteoMediaLinkService();
		$locale = $request->input('locale', 'en-CA');

		$longTerm = $link->getNext($locale, $country, $province, $city);

		if(!$longTerm) {
            return new Response(["could not contact Weather API"], 502);
        }

		$forecast = $longTerm->content["LongTermPeriod"][1];
		$forecast["Location"] = $longTerm->content["Location"];

		return new Response($forecast, 200);
	}

    /**
     * Give the seven days weather for the specified location
     *
     * @param Request $request The request
     * @param string  $country
     * @param string  $province
     * @param string  $city
     * @return Response
     */
	public function forecast(Request $request, string $country, string $province, string $city): Response
	{
        $this->sanitizeLocation($country, $province, $city);
		if(!$country || !$province || !$city) {
            return new Response(null);
        }

		$link = new MeteoMediaLinkService();
		$locale = $request->input('locale', 'en-CA');

		$forecast = $link->getNext($locale, $country, $province, $city);

        if(!$forecast) {
            return new Response(["could not contact Weather API"], 502);
        }

        $forecastData = $forecast->content;
        array_splice($forecastData["LongTermPeriod"], 0, 1);

		return new Response($forecast);
	}

    /**
     * Give the next hours weather forecast for the specified location
     *
     * @param Request $request The request
     * @param string  $country
     * @param string  $province
     * @param string  $city
     * @return Response
     */
    public function hourly(Request $request, string $country, string $province, string $city): Response
    {
        $this->sanitizeLocation($country, $province, $city);
        if(!$country || !$province || !$city) {
            return new Response(null);
        }

        $link = new MeteoMediaLinkService();
        $locale = $request->input('locale', 'en-CA');

        $hourly = $link->getHourly($locale, $country, $province, $city);

        if(!$hourly) {
            return new Response(["could not contact Weather API"], 502);
        }

        return new Response($hourly, 200);
    }


	public const PROVINCES = ["NL", "PE", "NS", "NB", "QC", "ON", "MB", "SK", "AB", "BC", "YT", "NT", "NU"];
	public const PROVINCES_LNG = [
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

	public const CITIES = [
		"Ville de Québec" => "Québec",
		"Boulevard Laurier" => "Québec",
		"Val-d'Or" => "Val Or",
		"L'Épiphanie" => "Epiphanie",
	];

	private function sanitizeLocation(String &$country, String &$province, String &$city): void {
		// Check if the location is valid
		if($country !== "CA") {
            $country = "CA";
        }

        if(array_key_exists($province, self::PROVINCES_LNG) && !in_array($province, self::PROVINCES, true)) {
            $province = self::PROVINCES_LNG[$province];
        }

		// Make sure the city format is valid
		$city = str_replace(".", "", urldecode($city));

//        Log::info($city);

		if(array_key_exists($city, self::CITIES)) {
            $city = self::CITIES[$city];
        }

		if($city === "Repentigny") {
            $province = 'QC';
        }
	}
}
