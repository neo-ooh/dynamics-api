<?php

namespace App\Http\Controllers;

use App\Cache;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;
use Illuminate\Support\Facades\Input;

class NationalController extends Controller
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
	 * @param Request $request
	 * @return Response
	 */
    public function get() {
		$forecasts = [];
		$link = new MeteoMediaLinkService();

		$locale = Input::get('locale', 'en-CA');

		foreach ($this->cities as $city) {
			array_push($forecasts, $link->getNow($locale, ...$city));
		}

		return new Response($forecasts);
    }
}
