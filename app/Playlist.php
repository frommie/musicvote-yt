<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
  protected $primaryKey = 'item_id';
  protected $keyType = 'string';
  protected $fillable = ['item_id'];

  public $incrementing = false;

  public function detail() {
    return $this->hasOne('App\Item', 'id', 'item_id');
  }

  public function votes() {
    return $this->hasMany('App\Vote', 'item_id', 'item_id');
  }

  public function uservote() {
    return $this->hasMany('App\Vote', 'item_id', 'item_id');
  }

  public function update_votes() {
    $this->upvotes = \App\Vote::where('item_id', $this->item_id)->where('vote', true)->count();
    $this->downvotes = \App\Vote::where('item_id', $this->item_id)->where('vote', false)->count();
    $this->save();
    Http\Controllers\Event::create('client', 'voted');
  }

  public static function boot() {
        parent::boot();

        static::deleting(function($playlist_item) {
             $playlist_item->votes()->delete();
        });
    }
}
