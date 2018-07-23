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

Route::get('/now/{country}/{province}/{city}', function (Request $request) {
    return $request->user();
});

Route::get('/tomorrow/{country}/{province}/{city}', function (Request $request) {
	return $request->user();
});

Route::get('/forecast/{country}/{province}/{city}', function (Request $request) {
	return $request->user();
});

Route::get('/national', function (Request $request) {
	$controller = new \App\Http\Controllers\NationalController();
	return $controller->get($request);
});
