<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Session;

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

  public function control(Request $request) {
    $response = new StreamedResponse(function() use ($request) {
    while(true) {
      // get event for session
      echo \App\Event::get(Session::getId());
      ob_flush();
      flush();
      usleep(2000000);
    }
    });
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('X-Accel-Buffering', 'no');
    $response->headers->set('Cach-Control', 'no-cache');
    return $response;
  }
}
