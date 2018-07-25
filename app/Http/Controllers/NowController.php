<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Services\MeteoMediaLinkService;
use Illuminate\Support\Facades\Input;

class NowController extends Controller
{
    public function get(String $country, String $province, String $city) {
	    $link = new MeteoMediaLinkService();

	    $locale = Input::get('locale', 'en-CA');

	    $now = $link->getNow($locale, $country, $province, $city);
	    $longTerm = $link->getNext($locale, $country, $province, $city)["LongTermPeriod"][0];

	    $forecast = array_merge($longTerm, $now);

	    return new Response($forecast);
    }
}
