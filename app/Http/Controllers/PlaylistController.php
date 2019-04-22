<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class PlaylistController extends Controller
{
  public function playlist() {
    $playlist = \App\Playlist::with('detail')->get();
    foreach ($playlist as $key => $item) {
      $playlist[$key]['votecount'] = \App\Vote::where('video_id', '=', $item->video_id)->count();
      $uservotes = \App\Vote::where('video_id', '=', $item->video_id)->where('session_id', '=', Session::getId())->first();
      if ($uservotes) {
        $playlist[$key]['vote'] = $uservotes->vote;
      } else {
        $playlist[$key]['vote'] = 0;
      }
    }
    return $playlist;
  }

  public function control() {
    // return events for session
  }
}
