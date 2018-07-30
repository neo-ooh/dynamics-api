<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Dynamic extends Model
{
	public function keys() {
		return $this->belongsToMany('App\Key', 'authorizations', 'dynamic', 'key');
	}
}
