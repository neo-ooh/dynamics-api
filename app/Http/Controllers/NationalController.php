<?php

namespace App\Http\Controllers;

use App\Cache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;

class NationalController extends Controller
{
	private $cities = [
		["CA", "BC", "Vancouver"],
		["CA", "ON", "Mississauga"],
		["CA", "AB", "Calgary"],
		["CA", "AB", "Edmonton"],
		["CA", "NS", "Halifax"],
		["CA", "BC", "Victoria"],
		["CA", "QC", "Quebec"],
		["CA", "QC", "Montreal"],
		["CA", "ON", "Ottawa"]
	];

	/**
	 * @param Request $request
	 * @return Response
	 */
    public function get(Request $request): Response {
    	$locale = "en-CA";
		$forecasts = [];
		$link = new MeteoMediaLinkService();

		foreach ($this->cities as $city) {
			array_push($forecasts, $link->getNow($locale, ...$city));
		}

		return new Response($forecasts);
    }
}
