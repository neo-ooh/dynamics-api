<?php

namespace App\Http\Controllers;

use App\WeatherBackground;
use App\WeatherLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WeatherBackgroundController extends Controller
{
	/**
	 * @return Response The list of cities with backgrounds sets
	 */
	public function registeredCities()
	{
		return new Response([
			'cities' => WeatherLocation::select('country', 'province', 'city')
				->orderBy('city')
				->groupBy('country', 'province', 'city')
				->get()
		]);
	}

	public function setSelectionMethod(Request $request) {
		$data = $request->validate([
			'country' => 'nullable|string|size:2',
			'province' => 'nullable|string|size:2',
			'city' => 'nullable|string|max:30',
			'selection' => 'required|string|max:10',
		]);

		$locationValues = $this->handleLocationValues($request);

		return new Response(WeatherLocation::UpdateOrCreate($locationValues, ['selection' => $data['selection']]));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$data = $request->validate([
			'country' => 'string|size:2',
			'province' => 'string|size:2',
			'city' => 'string|max:30',
			'period' => 'string|max:10',
			'support' => 'string|size:3',
		]);

		// Start by getting the canadians backgrounds
		$locationCanadaParams = ['country' => 'CA', 'province' => '--', 'city' => '-'];
		$locationCanada = WeatherLocation::firstOrCreate($locationCanadaParams, array_merge($locationCanadaParams, ['selection' => 'WEATHER']));

		$backgroundsCanada = WeatherBackground::where('location', $locationCanada->id)
			->when($request->period, function ($query) use ($request) {
				return $query->where('period', $request->period);
			})->when($request->support, function ($query) use ($request) {
				return $query->where('support', $request->support);
			})->when($locationCanada->selection === 'RANDOM', function ($query) use ($request) {
				return $query->where('weather', '-');
			})->get();

		// Get request location informations
		$locationReqParams = $this->handleLocationValues($request);

		// Is there a province in the request ?
		if($locationReqParams['province'] == "--") {
			// No province, we are not looking for more backgrounds. Let's stop here
			return new Response([
				'location' => $locationCanadaParams,
				'selection' => $locationCanada->selection,
				'backgrounds' => $backgroundsCanada]);
		}

		// There is a province, let's get its backgrounds
		$locationProvinceParams = ['country' => 'CA', 'province' => $locationReqParams['province'], 'city' => '-'];
		$locationProvince = WeatherLocation::firstOrCreate($locationProvinceParams, array_merge($locationProvinceParams, ['selection' => 'WEATHER']));

		$backgroundsProvince = WeatherBackground::where('location', $locationProvince->id)
			->when($request->period, function ($query) use ($request) {
				return $query->where('period', $request->period);
			})->when($request->support, function ($query) use ($request) {
				return $query->where('support', $request->support);
			})->when($locationProvince->selection === 'RANDOM', function ($query) use ($request) {
				return $query->where('weather', '-');
			})->get();

		$backgroundsProvince;

		// Is the province and canada are both on WEATHER selection method ?
		if($locationCanada->selection == $locationProvince->selection && $locationProvince->selection == 'WEATHER') {
			// Yes, let's merge the two sets
			$providedBackgrounds = [];
			foreach($backgroundsProvince as $background) {
				array_push($providedBackgrounds, $background->weather);
			}

			foreach($backgroundsCanada as $background) {
				if(!in_array($background->weather, $providedBackgrounds)) {
					$backgroundsProvince->push($background);
					array_push($providedBackgrounds, $background->weather);
				}
			}
		}

		// Is there a city in the request ?
		if($locationReqParams['city'] == "-") {
			// No city, we are not looking for more backgrounds. Let's stop here
			return new Response([
				'location' => $locationProvince,
				'selection' => $locationProvince->selection,
				'backgrounds' => $backgroundsProvince]);
		}

		//There is a city, let's get it's backgrounds
		$location = WeatherLocation::firstOrCreate($locationReqParams, array_merge($locationReqParams, ['selection' => 'WEATHER']));

		$backgroundsLocation = WeatherBackground::where('location', $location->id)
			->when($request->period, function ($query) use ($request) {
				return $query->where('period', $request->period);
			})->when($request->support, function ($query) use ($request) {
				return $query->where('support', $request->support);
			})->when($location->selection === 'RANDOM', function ($query) use ($request) {
				return $query->where('weather', '-');
			})->get();

		// Is the city and province are both on WEATHER selection method ?
		if($locationProvince->selection == $location->selection && $location->selection == 'WEATHER') {
			// Yes, let's merge the two sets
			$providedBackgrounds = [];
			foreach($backgroundsLocation as $background) {
				array_push($providedBackgrounds, $background->weather);
			}

			foreach($backgroundsProvince as $background) {
				if(!in_array($background->weather, $providedBackgrounds)) {
					$backgroundsLocation->push($background);
					array_push($providedBackgrounds, $background->weather);
				}
			}
		}

		return new Response([
			'location' => $location,
			'selection' => $location->selection,
			'backgrounds' => $backgroundsLocation]);
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request)
	{
		$data = $request->validate([
			'country' => 'required|string|size:2',
			'province' => 'required|string|size:2',
			'city' => 'required|string|max:30',
			'weather' => 'required|string|max:15',
			'period' => 'required|string|max:10',
			'support' => 'required|string|size:3',
			'background' => 'required|file|mimes:jpeg'
		]);

		$locationValues = $this->handleLocationValues($request);

		$locationValues = array_filter($locationValues, function($value) {
			return $value !== null;
		});

		$location = WeatherLocation::firstOrCreate($locationValues);

		// Check if a background for the same parameters exist
		$background = WeatherBackground::where('weather', $data['weather'])
			->where('period', $data['period'])
			->where('support', $data['support'])
			->whereHas('location', function ($query) use ($location) {
				$query->where('id', $location->id);
			})->first();

		// Remove old background
		if ($background) {
			$this->destroy($background);
			unset($background);
		}

		// Create the new background
		$background = new WeatherBackground();
		$background->weather = $data['weather'];
		$background->period = $data['period'];
		$background->support = $data['support'];
		$background->location = $location->id;
		$background->save();

		$path = Storage::disk('public')->putFileAs(
			'backgrounds', $request->file('background'), $background->id
		);

		return new Response([
			"success" => true,
			"background" => WeatherBackground::find($background->id),
			"path" => asset($path)
		]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\WeatherBackground $weatherBackground
	 * @return \Illuminate\Http\Response
	 */
	public function show(WeatherBackground $weatherBackground)
	{
		return new Response($weatherBackground);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\WeatherBackground $weatherBackground
	 * @return \Illuminate\Http\Response
	 * @throws \Exception
	 */
	public function destroy(WeatherBackground $weatherBackground)
	{
		Storage::disk('public')->delete("backgrounds/" . $weatherBackground->id);
		$weatherBackground->delete();

		return new Response([
			"success" => true
		]);
	}



	private function handleLocationValues($request) {
		return [
			'country' => $request->country ?: 'CA',
			'province' => $request->province ?: '--',
			'city' => $request->city ?: '-'
		];
	}
}
