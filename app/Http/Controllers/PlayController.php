<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlayController extends Controller
{
  public function play() {
    return view('play', ['video_id' => $this->first()]);
  }

  public function first() {
    // if video playing return playing video
    $item = \App\Playlist::where('playing', '=', true)->first();
    if ($item) {
      return $item->video_id;
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
    $max_item = \App\Playlist::orderBy('upvotes')->orderBy('created_at')->first();
    // return fallback if not exist
    if (!$max_item) {
      return $this->get_fallback_video();
    }
    // else
    $max_item->playing = true;
    $max_item->save();
    return $max_item->video_id;
  }

  protected function get_fallback_video() {
    return "qmsbP13xu6k";
  }

}
