<?php

namespace App\Http\Middleware;

use App\AuthToken;
use Closure;
use \Illuminate\Http\Response;

class UserTokenVerification
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
		$userToken = $request->header('Authorization', null);

		if (!$userToken) {
			return $this->onError('An authentification token is required for this operation.');
		}

		$token = AuthToken::where('token', $userToken)->first();

		if (!$token) {
			return $this->onError("Invalid authentication token");
		}

		if ($token['ip'] != $request->ip()) {
			$token->delete();
			return $this->onError("You cannot use this token.");
		}

		if ($token['updated_at']->getTimestamp() + config('cache.user_session_resilience') < time()) {
			$token->delete();
			return $this->onError("This token is outdated.");
		}

		// Token is ok, refresh resilience
		$token->touch();

		// Store user ID in the request
		$request->attributes->add(['user' => $token['user']]);

		return $next($request);
	}

	private function onError(string $msg)
	{
		return new Response([
			"content" => [
				"error" => true,
				"message" => $msg
			]
		], 401);
	}
}
