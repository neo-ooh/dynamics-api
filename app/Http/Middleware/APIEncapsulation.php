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
        /**
         * Illuminate\Http\Response
         */
		$response = $next($request);
		$responseJSON = json_decode($response->content(), true) ?: [];

		if(array_key_exists('content', $responseJSON))
			$responseJSON = $responseJSON['content'];

		$factor = rand(90, 110) / 100;

		$formated = [
			"timestamp" => time(),
			"refresh" => config('cache.record_lifespan', 0) * $factor,
			"content" => $responseJSON,
			"status" => $response->getStatusCode()
		];

		$response->setContent(json_encode($formated));
		$response->setMaxAge(config('cache.record_lifespan', 0) * $factor);

		return $response;
	}

}
