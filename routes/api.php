<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*
 * DYNAMIC Main API
 */
Route::resource('keys', 'KeysController')->only([
	'index', 'show', 'store', 'edit', 'update', 'destroy'
]);


///////////////
// The Dynamics

/**
 * Weather dynamic routes
 */
Route::group(['prefix' => 'weather', 'middleware' => 'APIencapsulation'], function () {
	Route::get('now/{country}/{province}/{city}', "WeatherController@now")->name("weather.now");
	Route::get('tomorrow/{country}/{province}/{city}', "WeatherController@tomorrow")->name("weather.tomorrow");
	Route::get('forecast/{country}/{province}/{city}', "WeatherController@forecast")->name("weather.forecast");
	Route::get('national', "WeatherController@national")->name("weather.national");
});

