<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class PlaylistController extends Controller
{
  public function playlist() {
    $playlist = \App\Playlist::with('detail')->orderBy('upvotes')->orderBy('created_at')->get();
    foreach ($playlist as $key => $item) {
      $playlist[$key]['votecount'] = $item->upvotes - $item->downvotes;
      $uservotes = \App\Vote::where('video_id', '=', $item->video_id)->where('session_id', '=', Session::getId())->first();
      if ($uservotes) {
        $playlist[$key]['vote'] = $uservotes->vote;
      } else {
        $playlist[$key]['vote'] = 0;
      }
    }
    return $playlist;
  }
}
