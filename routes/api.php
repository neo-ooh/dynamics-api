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

Route::get('/now/{country}/{province}/{city}', "NowController@get");
Route::get('/tomorrow/{country}/{province}/{city}', "TomorrowController@get");
Route::get('/forecast/{country}/{province}/{city}', "ForecastController@get");
Route::get('/national', "NationalController@get");
