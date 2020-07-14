<?php

namespace App\Http\Controllers;

use App\WeatherBackground;
use App\WeatherLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
		$request->validate([
			'country' => 'nullable|string|size:2',
			'province' => 'nullable|string|size:2',
			'city' => 'nullable|string|max:30',
			'selection' => 'required|string|max:10',
            'revertDate' => 'nullable|int|digits_between:1,11'
		]);

		$locationValues = $this->handleLocationValues($request);

		return new Response(WeatherLocation::UpdateOrCreate(
		    $locationValues,
            ['selection' => $request->selection,
             'revert_date' => $request->revertDate]));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @return \Illuminate\Http\Response
	 */
	public function index(Request $request)
	{
		$request->validate([
			'country' => 'string|size:2',
			'province' => 'string|size:2',
			'city' => 'string|max:30',
			'period' => 'string|max:10',
			'support' => 'string|size:3',
		]);

		// Start by getting the locations (country, province if specified, and city if specified)

        $locationParams = [
            'country' => $request->country?: 'CA',
            'province' => $request->province != 'null'?: '--',
            'city' => $request->city != 'null' ?: '-'
        ];

        if($locationParams['city'] === 'Repentigny') {
            $locationParams['province'] = 'QC';
        }

        Log::debug('location provided:', $locationParams);

        // We do a INSERT IGNORE with the given location to ensure its presence in the ddb
        WeatherLocation::firstOrCreate(
            ['country' => $locationParams['country'],
            'province' => $locationParams['province'],
            'city' => $locationParams['city']]);

        // Get the locations
        $locations = WeatherLocation::where('country', $locationParams['country'])
            ->whereIn('province', array('--', $locationParams['province']))
            ->whereIn('city', array('-', $locationParams['city']))
            ->orderBy('id')
            ->get();

        $isRandom = false;
        $randomLocation = -1;

        // Check if there is a random switch on
        foreach ($locations as $location) {
            if($location->selection == "RANDOM") {

                // Check if the revert date is passed
                if($location->revert_date < time()) {
                    // This location should be reverted to WEATHER
                    $location->selection = "WEATHER";
                    $location->save();

                    continue;
                }

                $isRandom = true;
                $randomLocation = $location;
                break;
            }
        }

        $locationsID = [];
        foreach ($locations as $loc) {
            array_push($locationsID, $loc->id);
        }

        $location = $locations->last();

        if($isRandom) {
            // Select all the random backgrounds for the specific location
            $backgrounds = WeatherBackground::where('location', $randomLocation->id)
                ->where('period', 'ALL')
                ->where('support', $request->support)
                ->where('weather', '-')
                ->get();

            return new Response([
                'location' => $randomLocation,
                'selection' => 'RANDOM',
                'backgrounds' => $backgrounds]);
        }

        // Get the backgrounds for the current location for all periods
        $allBackgrounds = WeatherBackground::listByParameters($locationsID, $request->support, 'ALL')->toArray();

        if($request->period != 'ALL') {
            // Get the background for the requested period
            $periodBackgrounds = WeatherBackground::listByParameters($locationsID, $request->support, $request->period)->toArray();

            // Merge the backgrounds for 'ALL' periods with the backgrounds for the specified period if needed
            // A higher location ID means a more precise location
            foreach ($allBackgrounds as $aBckg) {
                $foundEquivalent = false;

                foreach($periodBackgrounds as $bckgKey => $pBckg) {
                    if($pBckg['weather'] == $aBckg['weather'])
                        $foundEquivalent = true;

                    if($pBckg['weather'] == $aBckg['weather'] && $pBckg['location']['id'] < $aBckg['location']['id']) {
                        $periodBackgrounds[$bckgKey] = $aBckg;
                    }
                }

                if(!$foundEquivalent) {
                    array_push($periodBackgrounds, $aBckg);
                }
            }

            // replace the allBackgrounds variable
            $allBackgrounds = $periodBackgrounds;
        }

        return new Response([
			'location' => $location,
			'selection' => $location->selection,
			'backgrounds' => $allBackgrounds]);
	}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
	public function store(Request $request)
	{
		$data = $request->validate([
			'location' => 'required|int',
			'weather' => 'required|string|max:15',
			'period' => 'required|string|max:10',
			'support' => 'required|string|size:3',
			'background' => 'required|file|mimes:jpeg'
		]);

		// Check if a background for the same parameters exist
		if($data['weather'] !== '-') {
			$background = WeatherBackground::where('weather', $data['weather'])
				->where('period', $data['period'])
				->where('support', $data['support'])
				->where('location', $data['location'])
				->first();

			// Remove old background
			if ($background) {
				$this->destroy($background);
				unset($background);
			}
		}

		// Create the new background
		$background = new WeatherBackground();
		$background->weather = $data['weather'];
		$background->period = $data['period'];
		$background->support = $data['support'];
		$background->location = $data['location'];
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
	public function destroy($weatherBackground)
	{
		if(is_numeric($weatherBackground))
			$weatherBackground = WeatherBackground::find($weatherBackground);

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
