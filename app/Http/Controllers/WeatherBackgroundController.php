<?php

namespace App\Http\Controllers;

use App\WeatherBackground;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class WeatherBackgroundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
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
		    'support' =>  'string|size:3',
		    'background' => 'file'
	    ]);

	    return new Response(
	    	WeatherBackground::where($data)->get()
	    );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
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
	        'support' =>  'required|string|size:3',
	        'background' => 'required|file'
        ]);

        // Check if a background for the same parameters exist
	    $background = WeatherBackground::where('country', $data['country'])
		    ->where('province', $data['province'])
		    ->where('city', $data['city'])
		    ->where('weather', $data['weather'])
		    ->where('period', $data['period'])
	        ->where('support', $data['support'])
		    ->first();

	    // Remove old background
	    if($background) {
	    	$this->destroy($background);
	    	unset($background);
	    }

	    // Create the new background
	    $background = new WeatherBackground();
	    $background->country = $data['country'];
	    $background->province = $data['province'];
	    $background->city = $data['city'];
	    $background->weather = $data['weather'];
	    $background->period = $data['period'];
	    $background->support = $data['support'];
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
     * @param  \App\WeatherBackground  $weatherBackground
     * @return \Illuminate\Http\Response
     */
    public function show(WeatherBackground $weatherBackground)
    {
        return new Response($weatherBackground);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\WeatherBackground  $weatherBackground
     * @return \Illuminate\Http\Response
     */
    public function destroy(WeatherBackground $weatherBackground)
    {
	    Storage::disk('public')->delete("backgrounds/" . $weatherBackground->id);
	    $weatherBackground->delete();

	    return new Response([
		    "success" => true
	    ]);
    }


    // NO CRUD METHODS

	public function registeredCities() {
    	return new Response([
    		'cities' => WeatherBackground::orderBy('city')
			    ->groupBy('city')->pluck('city')->toArray()
	    ]);
	}
}
