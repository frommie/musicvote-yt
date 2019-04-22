<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class ItemController extends Controller
{
  public function vote (Request $request) {
    $data = $request->validate([
      'video_id' => 'required|max:255',
    ]);
    $vote = new \App\Vote();
    $vote->video_id = (string)$data['video_id'];
    $vote->session_id = Session::getId();
    $vote->vote = 1;
    $vote->save();
  }
}
