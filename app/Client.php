<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
  public $incrementing = false;
  
  public function events() {
    return $this->hasMany('App\Event');
  }

  public function votes() {
    return $this->hasMany('App\Vote');
  }
}
