<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;
use Illuminate\Support\Facades\Input;

class ForecastController extends Controller
{
	public function get(String $country, String $province, String $city) {
		$link = new MeteoMediaLinkService();

		$locale = Input::get('locale', 'en-CA');

		$forecast = $link->getNext($locale, $country, $province, $city);
		array_splice($forecast["LongTermPeriod"], 0, 1);

		return new Response($forecast);
	}
}
