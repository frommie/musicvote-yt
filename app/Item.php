<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
  protected $keyType = 'string';

  public $incrementing = false;

  public function playlist() {
    return $this->belongsTo('App\Playlist', 'id', 'video_id');
  }
}
