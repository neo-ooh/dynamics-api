<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Key extends Model
{
    public function authorizations() {
    	return $this->hasMany('App\Authorization', 'key');
    }

    public function dynamics() {
    	return $this->belongsToMany('App\Dynamic', 'authorizations', 'key', 'dynamic');
    }
}
