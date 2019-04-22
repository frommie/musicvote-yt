<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class ItemController extends Controller
{
  public function vote(Request $request, $id) {
    $data = $request->validate([
      'vote' => 'required',
    ]);
    $vote = \App\Vote::where('session_id', '=', Session::getId())->where('video_id', '=', $id)->first();
    if ($vote) {
      // update vote
      $vote->vote = $data['vote'];
    } else {
      $vote = new \App\Vote();
      $vote->video_id = $id;
      $vote->session_id = Session::getId();
      $vote->vote = $data['vote'];
    }
    $vote->save();

    // check if already in playlist
    $playlist = \App\Playlist::where('video_id', '=', $id)->first();
    if (!$playlist) {
      $item = \App\Playlist::create(['video_id' => $id]);
    }
    return "test";
  }
}
