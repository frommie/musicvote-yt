<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
  protected $primaryKey = 'session_id';
  protected $keyType = 'string';
  protected $fillable = ['session_id'];

  public function events() {
    return $this->hasMany('App\Event');
  }

  public function votes() {
    return $this->hasMany('App\Vote');
  }
}
