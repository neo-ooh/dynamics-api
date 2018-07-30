<?php

namespace App\Http\Controllers;

use App\Authorization;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Key;

class KeysController extends Controller
{
    public function index() {
		return new Response(Key::with('dynamics')->get());
    }

    public function show(int $keyID) {
    	$key = Key::where('id', $keyID)->with('dynamics')->first();
    	return new Response($key);
    }

	public function store(Request $request) {
		$data = $request->validate([
			'name' => 'required|string|min:3|unique:keys',
			'dynamics' => 'sometimes|array',
			'dynamics.*' => 'integer|distinct|exists:dynamics,id'
		]);

		$key = new Key();
		$key->name = $data['name'];
		$key->key = bin2hex(random_bytes(32));

		$key->save();

		foreach ($data['dynamics'] as $dynamicID) {
			$auth = new Authorization();
			$auth->key = $key->id;
			$auth->dynamic = $dynamicID;
			$auth->save();
		}

		return $this->show($key->id);
	}

	public function update(int $keyID, Request $request) {
		$data = $request->validate([
			'name' => 'required|string|min:3'
		]);

		// Check for duplicate name
		$keys = Key::where('name', $data['name'])->get();

		if(count($keys) != 0) {
			// Name already used
			if($keys[0]->id != $keyID)
				return new Response([
					"message" => "The given data was invalid.",
	                "errors" => [
	                    "name" => [
							"The name is already taken"
						]
	                ]
				], 422);
			else
				return $this->show($keyID); // Same name, do nothing
		}

		$key = Key::where('id', $keyID)->first();
		$key->name = $data['name'];
		$key->save();

		return $this->show($keyID);
	}

	public function destroy(int $keyID) {
		$key = Key::destroy($keyID);
	}
}
