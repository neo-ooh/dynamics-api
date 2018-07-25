<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;
use Illuminate\Support\Facades\Input;

class TomorrowController extends Controller
{
	public function get(String $country, String $province, String $city) {
		$link = new MeteoMediaLinkService();

		$locale = Input::get('locale', 'en-CA');

		$longTerm = $link->getNext($locale, $country, $province, $city);
		$forecast = $longTerm["LongTermPeriod"][1];
		$forecast["Location"] = $longTerm["Location"];

		return new Response($forecast);
	}
}
