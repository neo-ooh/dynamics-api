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

/**
 * Weeather dynamic routes
 */
Route::prefix('weather')->group(function () {
	Route::get('now/{country}/{province}/{city}', "WeatherController@now");
	Route::get('tomorrow/{country}/{province}/{city}', "WeatherController@tomorrow");
	Route::get('forecast/{country}/{province}/{city}', "WeatherController@forecast");
	Route::get('national', "WeatherController@national");
});
