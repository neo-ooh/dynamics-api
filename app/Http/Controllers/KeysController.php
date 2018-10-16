<?php

namespace App\Http\Controllers;

use App\Authorization;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Key;

class KeysController extends Controller
{
	function __construct() {
		$this->middleware('UserTokenVerification');
	}
	/**
	 * @return Response
	 */
	public function index()
	{
		return new Response(Key::with('dynamics')->get());
	}

	/**
	 * @param Key $key
	 * @return Response
	 */
	public function show(Key $key)
	{
		return new Response($key);
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @throws \Exception
	 */
	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required|string|min:3|unique:keys',
			'dynamics' => 'array',
			'dynamics.*' => 'integer|distinct|exists:dynamics,id'
		]);

		$key = new Key();
		$key->name = $request->name;
		$key->key = bin2hex(random_bytes(32));

		$key->save();

		if($request->dynamics) {
			foreach ($request->dynamics as $dynamicID) {
				$auth = new Authorization();
				$auth->key = $key->id;
				$auth->dynamic = $dynamicID;
				$auth->save();
			}
		}

		return $this->show($key);
	}

	public function update(Key $key, Request $request)
	{
		$data = $request->validate([
			'name' => 'required|string|min:3',
			'dynamics' => 'array',
			'dynamics.*' => 'integer|distinct|exists:dynamics,id'
		]);

		// Check for duplicate name
		$keys = Key::where('name', $data['name'])->get();

		if (count($keys) != 0) {
			// Name already used
			if ($keys[0]->id != $key->id) {
				return new Response([
					"message" => "The given data was invalid.",
					"errors" => [
						"name" => [
							"The name is already taken"
						]
					]
				], 422);
			}
		}

		$key->name = $data['name'];
		$key->save();

		$dynamics = $request->dynamics ?: [];

		// Delete all authorizations then do them again
		Authorization::where('key', $key->id)->delete();

		foreach ($request->dynamics as $dynamicID) {
			$auth = new Authorization();
			$auth->key = $key->id;
			$auth->dynamic = $dynamicID;
			$auth->save();
		}

		$key->refresh();

		return $this->show($key);
	}

	/**
	 * @param Key $key
	 */
	public function destroy(Key $key)
	{
		$key = Key::destroy($key);
	}
}
