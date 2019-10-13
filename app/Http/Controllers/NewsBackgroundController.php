<?php

namespace App\Http\Controllers;

use App\NewsBackground;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class NewsBackgroundController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $support
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->indexForSupport($request, $request['support']);
    }

    public function indexForSupport(Request $request, string $support) {
        // Get all the backgrounds for the specified support and locale

        return new Response(
            NewsBackground::where('support', $support)
//                          ->where('locale', $data['locale'])
                ->with('category')
                ->get()
        );
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
			'category' => 'required|int',
			'support' => 'required|string|size:3',
			'locale' => 'required|string|max:5',
			'background' => 'required|file|mimes:jpeg,png'
		]);

		// Check if a background for the same parameters exist
        $background = NewsBackground::where('category', $data['category'])
            ->where('support', $data['support'])
            ->where('locale', $data['locale'])
            ->first();

        // Remove old background
        if ($background) {
            $this->destroy($background);
            unset($background);
        }


		// Create the new background
		$background = new NewsBackground();
		$background->category = $data['category'];
		$background->support = $data['support'];
		$background->locale = $data['locale'];
		$background->save();

		$path = Storage::disk('public')->putFileAs(
			'news/backgrounds', $request->file('background'), $background->id
		);

		$background->load('category');

		return new Response([
            "background" => $background,
		]);
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  \App\WeatherBackground $weatherBackground
	 * @return \Illuminate\Http\Response
	 */
	public function show(NewsBackground $newsBackground)
	{
		return new Response($newsBackground);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  \App\WeatherBackground $weatherBackground
	 * @return \Illuminate\Http\Response
	 * @throws \Exception
	 */
	public function destroy(NewsBackground $background)
	{
		Storage::disk('public')->delete("news/backgrounds/" . $background->id);
        $background->delete();

		return new Response([
			"success" => true
		]);
	}
}
