<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PlayController extends Controller
{
  public function first() {
    $first_item = \App\Playlist::all()->max('votes');
    $playlist = \App\Playlist::find($first_item[0]->video_id);
    $playlist->playing = true;
    $playlist->save();
    return $first_item[0]->video_id;
  }
}
