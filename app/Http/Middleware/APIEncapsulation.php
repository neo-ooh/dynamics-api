<?php

namespace App\Http\Middleware;

use Illuminate\Http\Response;
use Closure;

class APIEncapsulation
{

	/**
	 * Handle an incoming request.
	 *
	 * @param  \Illuminate\Http\Request $request
	 * @param  \Closure                 $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = json_decode($next($request)->content(), true);
		$formated = [
			"timestamp" => time(),
			"refresh" => env('RECORD_LIFESPAN', 0),
			"content" => $response];

		return new Response($formated);
	}

}
