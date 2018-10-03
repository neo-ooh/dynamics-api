<?php

namespace App\Http\Controllers;

use App\AuthToken;
use App\User;
use Illuminate\Database\Console\Migrations\ResetCommand;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
	public function login(Request $request)
	{
		$data = $request->validate([
			'email' => 'required|string',
			'password' => 'required|string',
		]);

		$user = User::where('user_email', $data['email'])
			->where('user_admin', true)
			->where('user_live', true)
			->first();

		if (!$user) {
			return $this->onError("Given user email is invalid");
		}

		if (!password_verify($data['password'], $user['user_password'])) {
			return $this->onError('Given password is incorrect');
		}

		// Correct login information
		// Remove existing token that may be left
		AuthToken::where('user', $user['user_id'])->delete();

		// generate new token
		$token = bin2hex(random_bytes(32));

		//Insert token
		$authToken = new AuthToken();
		$authToken->user = $user['user_id'];
		$authToken->token = $token;
		$authToken->ip = $request->ip();
		$authToken->save();

		return new Response([
			'success' => true,
			'token' => $token,
			'name' => $user['user_name']
		]);
	}


	public function logout(Request $request)
	{
		AuthToken::where('user', $request->get('user'))->delete();

		return new Response([
			'success' => true
		]);
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
