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
  }

  public function downvote($id) {
    $this->vote($id, false);
  }

  protected function vote($id, $up) {
    $vote = \App\Vote::where('session_id', '=', Session::getId())->where('item_id', '=', $id)->first();
    if ($vote) {
      // update vote
      $vote->vote = $up;
    } else { // create new vote
      $vote = new \App\Vote();
      $vote->item_id = $id;
      $vote->session_id = Session::getId();
      $vote->vote = $up;
    }
    $vote->save();

    $item = \App\Playlist::firstOrCreate(['item_id' => $id]);
    $item->update_votes();

    // if votes < 0: Remove from playlist and skip if playing
    if (($item->upvotes - $item->downvotes) < 0) {
      if ($item->playing) { // skip if playing
        Event::create('player', 'skip');
      }
      $item->delete();
    }
  }
}
