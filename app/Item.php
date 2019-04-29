<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
  protected $keyType = 'string';
  protected $fillable = ['id', 'title', 'img'];

  public $incrementing = false;

  public function playlist() {
    return $this->belongsTo('App\Playlist', 'id', 'item_id');
  }

  public function votes() {
    return $this->hasMany('App\Vote', 'item_id');
  }
}
