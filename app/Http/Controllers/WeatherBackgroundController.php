<?php

namespace App\Http\Controllers;

use App\WeatherBackground;
use App\WeatherLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class WeatherBackgroundController extends Controller
{
	/**
	 * @return Response The list of cities with backgrounds sets
	 */
	public function registeredCities()
	{
		return new Response([
			'cities' => WeatherLocation::orderBy('city')
				->groupBy('city')->pluck('city')->toArray()
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

		return new Response(WeatherLocation::firstOrCreate($locationValues, $data));
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
			'weather' => 'string|max:15',
			'period' => 'string|max:10',
			'support' => 'string|size:3',
		]);

		$backgrounds = WeatherBackground::when($request->weather, function ($query) use ($request) {
			return $query->where('weather', $request->weather);
		})->when($request->period, function ($query) use ($request) {
			return $query->where('period', $request->period);
		})->when($request->support, function ($query) use ($request) {
			return $query->where('support', $request->support);
		})->with(['location' => function($query) use ($request)
		{
			return $query->when($request->country, function ($query) use ($request) {
				return $query->where('country', $request->country);
			})->when($request->province, function ($query) use ($request) {
				return $query->where('province', $request->province);
			})->when($request->city, function ($query) use ($request) {
				return $query->where('city', $request->city);
			});
		}])->get();

		return new Response($backgrounds);
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
			'country' => 'string|size:2',
			'province' => 'string|size:2',
			'city' => 'string|max:30',
			'weather' => 'required|string|max:15',
			'period' => 'required|string|max:10',
			'support' => 'required|string|size:3',
			'background' => 'required|file'
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
			->with(['location' => function ($query) use ($location) {
				$query->where('id', '=', $location->id);
			}])->first();

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
		$background->location = $location;
		$background->save();

		$path = Storage::disk('public')->putFileAs(
			'backgrounds', $request->file('background'), $background->id
		);

		return new Response([
			"success" => true,
			"background" => $this->show($background)->getOriginalContent(),
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
			'country' => $request->country ?: null,
			'province' => $request->province ?: null,
			'city' => $request->city ?: null,
			'selection' => 'WEATHER',
		];
	}
}
