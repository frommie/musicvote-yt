<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class VoteController extends Controller
{
  public function get() {
    return response()->json(\App\Vote::where('session_id', '=', Session::getId())->get());
  }

  public function upvote($id) {
    $this->vote($id, true);
    $this->update_votes($id);
  }

  public function downvote($id) {
    $this->vote($id, false);
    $this->update_votes($id);
  }

  protected function vote($id, $up) {
    $vote = \App\Vote::where('session_id', '=', Session::getId())->where('video_id', '=', $id)->first();
    if ($vote) {
      // update vote
      $vote->vote = $up;
    } else {
      $vote = new \App\Vote();
      $vote->video_id = $id;
      $vote->session_id = Session::getId();
      $vote->vote = $up;
    }
    $vote->save();

    // check if already in playlist
    $playlist = \App\Playlist::where('video_id', '=', $id)->first();
    if (!$playlist) { // not in playlist yet - add
      $item = \App\Playlist::create(['video_id' => $id]);
    }

    // if playing and votes < 0: Remove from playlist and skip
    $item = \App\Item::find($id);
    $playing = \App\Playlist::where('playing', '=', true)->first();
    $votecount = 0;
    foreach ($item->votes as $vote) {
      $votecount += $vote->vote;
    }
    if ($votecount < 0 && $item->id == $playing->video_id) {
      $playing->delete();
      Event::create('player', 'skip');
    }
  }

  protected function update_votes($id) {
    $item = \App\Playlist::find($id);
    $item->upvotes = \App\Vote::where('video_id', $id)->where('vote', true)->count();
    $item->downvotes = \App\Vote::where('video_id', $id)->where('vote', false)->count();
    $item->save();
    Event::create('client', 'voted');
  }
}
