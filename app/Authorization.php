<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    public function key() {
    	return $this->belongsTo('App\Key', 'key', 'id');
    }
}
