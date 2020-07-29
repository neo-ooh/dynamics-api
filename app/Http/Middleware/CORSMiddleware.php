<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Response;

class CORSMiddleware
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
		// ALLOW OPTIONS METHOD
		$headers = [
		    'Access-Control-Allow-Origin' => '*',
			'Access-Control-Allow-Methods' => 'POST, GET, OPTIONS, PUT, PATCH, DELETE',
			'Access-Control-Allow-Headers' => 'Content-Type, X-Auth-Token, Origin, pragma, cache-control, Authorization, cache'
		];

		if ($request->getMethod() == "OPTIONS") {
			// The client-side application can set only headers allowed in Access-Control-Allow-Headers
			return Response::make('OK', 200);
		}

		$response = $next($request);

//		foreach ($headers as $key => $value) {
//			$response->header($key, $value);
//		}

		return $response;
	}

}
