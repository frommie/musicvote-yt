<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BackendController extends Controller
{
  public function search(Request $request) {
    $data = $request->validate([
      'query' => 'required|max:255',
    ]);
    $youtube_api = new \App\YoutubeAPI();
    $result = $youtube_api->search($data['query']);

    return $result;
  }
}
