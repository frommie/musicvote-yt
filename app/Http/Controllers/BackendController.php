<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Session;

class BackendController extends Controller
{
  public function control(Request $request) {
    $response = new StreamedResponse(function() use ($request) {
      echo "\n\n";
      ob_flush();
      flush();
      while(true) {
        // get event for session
        $event = Event::get(Session::getId());
        echo "data: {$event}\n\n";
        ob_flush();
        flush();
        usleep(200000);
      }
    });
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('X-Accel-Buffering', 'no');
    $response->headers->set('Cach-Control', 'no-cache');
    return $response;
  }

  public function search(Request $request) {
    $data = $request->validate([
      'query' => 'required|max:255',
    ]);
    $service = \App\Conf::get('service');
    if ($service == 'spotify') {
      $result = \App\SpotifyAPI::search($data['query']);
    } else {
      $api = new \App\YoutubeAPI();
      $result = $api->search($data['query']);
    }

    return $result;
  }

  public function auth() {
    $spotify_api = new \App\SpotifyAPI();
    return redirect()->away($spotify_api->auth());
  }

  public function callback(Request $request) {
    $spotify_api = new \App\SpotifyAPI();
    $code = $request->query('code');
    $spotify_api->callback($code);
    return redirect('/');
  }

  public function test() {
    $test = \App\SpotifyAPI::search("keine parolen");
    return response()->json($test);
  }
}
