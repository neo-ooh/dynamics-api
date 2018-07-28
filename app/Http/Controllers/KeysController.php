<?php

namespace App\Http\Controllers;

use App\Authorization;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Key;

class KeysController extends Controller
{
    public function index() {
		return new Response(Key::with('authorizations')->get());
    }

    public function show(int $keyID) {
    	$key = Key::where('id', $keyID)->with('authorizations')->first();
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

	public function edit(int $keyID) {

	}

	public function update(int $keyID) {

	}

	public function destroy(int $keyID) {
		$key = Key::destroy($keyID);
	}
}
