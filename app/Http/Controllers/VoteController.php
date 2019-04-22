<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;

class VoteController extends Controller
{
  public function get() {
    return response()->json(\App\Vote::where('session_id', '=', Session::getId())->get());
  }
}
