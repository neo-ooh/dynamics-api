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
		$request->validate([
			'country' => 'string|size:2',
			'province' => 'string|size:2',
			'city' => 'string|max:30',
			'period' => 'string|max:10',
			'support' => 'string|size:3',
		]);

		// Start by getting the locations (country, province if specified, and city if specified)

        $locationParams = [
            'country' => $request->country ?: 'CA',
            'province' => $request->province ?: '--',
            'city' => $request->city ?: '-'
        ];

        // We do a INSERT IGNORE with the given location to ensure its presence in the ddb
        WeatherLocation::firstOrCreate(array('country' => $locationParams['country'],
                                             'province' => $locationParams['province'],
                                             'city' => $locationParams['city']));

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
                $isRandom = true;
                $randomLocation = $location;
                break;
            }
        }

        $locationsID = [];
        foreach ($locations as $location) {
            array_push($locationsID, $location->id);
        }

        $location = $locations->last();

        if($isRandom) {
            // Select all the random backgrounds for the specific location
            $backgrounds = WeatherBackground::where('location', $randomLocation->id)
                ->where('period', $request->period)
                ->where('support', $request->support)
                ->get();

            return new Response([
                'location' => $location,
                'selection' => $location->selection,
                'backgrounds' => $backgrounds]);
        }

        // Get the backgrounds for the current location for all periods
        $allBackgrounds = WeatherBackground::listByParameters($locationsID, $request->support, 'ALL')->get()->toArray();

        if($request->period != 'ALL') {
            // Get the background for the requested period
            $periodBackgrounds = WeatherBackground::listByParameters($locationsID, $request->support, $request->period)->get()->toArray();

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
		if($data['weather'] !== '-') {
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
