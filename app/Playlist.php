<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
  protected $primaryKey = 'video_id';
  protected $keyType = 'string';
  protected $fillable = ['video_id'];

  public $incrementing = false;

  public function detail() {
    return $this->hasOne('App\Item', 'id', 'video_id');
  }

  public function votes() {
    return $this->hasMany('App\Vote', 'video_id', 'video_id');
  }

  public function uservote() {
    return $this->hasMany('App\Vote', 'video_id', 'video_id');
  }
}
