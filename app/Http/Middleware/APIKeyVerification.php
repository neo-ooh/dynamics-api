<?php

namespace App\Http\Middleware;

use App\Dynamic;
use App\Key;
use Closure;
use Illuminate\Http\Response;

class APIKeyVerification
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, string $dynamic)
    {
	    $key = $request->key;

	    // Check key presence
	    if($key == null)
	    	return $this->onError("An API key is required to use Neo-Traffic's Dynamics API");

	    $keyInfos = Key::where('key', $key)->first();

	    // Confirm key exists
	    if(!$keyInfos)
		    return $this->onError("The given API Key is not recognized");

	    // Confirm key is authorized for current dynamic
	    $dynamic = $keyInfos->dynamics()->where('slug', $dynamic)->first();

		if(!$dynamic)
			return $this->onError("The given API Key is not authorized for this dynamic");

		// Key is OK
        return $next($request);
    }

    private function onError(string $msg) {
    	return new Response([
    		"content" => [
    	    	"error" => true,
		        "message" => $msg
		    ]
	    ], 401);
    }
}
