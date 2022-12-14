<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class WeatherControlMiddleware {
    public function handle(Request $request, Closure $next) {

        // Disabled
//        return new Response(null, 429);

        //Enabled
        return $next($request);
    }
}
