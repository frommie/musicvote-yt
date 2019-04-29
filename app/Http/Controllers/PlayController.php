<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlayController extends Controller
{
  public function play() {
    return view('play', ['item_id' => $this->first()]);
  }

  public function first() {
    // if video playing return playing video
    $item = \App\Playlist::where('playing', '=', true)->first();
    if ($item) {
      return $item->item_id;
    }
    // else get video by max votes
    return $this->get_top_video();
  }

  public function next() {
    $item = \App\Playlist::where('playing', '=', true)->first();
    if ($item) {
      $item->delete();
    }
    return $this->get_top_video();
  }

  protected function get_top_video() {
    $playlist_top_item = \App\Playlist::orderBy('upvotes')->orderBy('created_at')->first();
    // return fallback if not exist
    if (!$playlist_top_item) {
      $random_item = $this->get_fallback_video();
      if ($random_item) {
        $playlist_top_item = \App\Playlist::create(['item_id' => $random_item->id]);
      } else {
        $this->load_fallback_playlist();
        $random_item = $this->get_fallback_video();
        if ($random_item) {
          $playlist_top_item = \App\Playlist::create(['item_id' => $random_item->id]);
        } else {
          $playlist_top_item = \App\Playlist::create(['item_id' => "JSPKUUdj6K4"]);
        }
      }
    }
    // else
    $playlist_top_item->playing = true;
    $playlist_top_item->save();
    return $playlist_top_item->item_id;
  }

  public function load_fallback_playlist() {
    // retrieve items and save
    $fallback_playlist_id = config('services.youtube.playlist');
    if ($fallback_playlist_id != "") {
      $api = new \App\YoutubeAPI();
      $results = $api->get_playlist_items($fallback_playlist_id);
      foreach ($results as $result) {
        $result->fallback = 1;
        $result->save();
      }
    }
  }

  protected function get_fallback_video() {
    // get items where fallback = true
    $fallback_item = \App\Item::where('fallback', '=', '1')->inRandomOrder()->first();
    return $fallback_item;
  }

}
