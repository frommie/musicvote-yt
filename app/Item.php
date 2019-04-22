<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
  protected $keyType = 'string';
  protected $fillable = ['id', 'title', 'img_url'];

  public $incrementing = false;

  public function playlist() {
    return $this->belongsTo('App\Playlist', 'id', 'video_id');
  }
}
