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

Route::get('/now/{country}/{province}/{city}/{locale?}', function (Request $request) {
    return $request->user();
});

Route::get('/tomorrow/{country}/{province}/{city}/{locale?}', function (Request $request) {
	return $request->user();
});

Route::get('/forecast/{country}/{province}/{city}/{locale?}', function (Request $request) {
	return $request->user();
});

Route::get('/national', "NationalController@get");
