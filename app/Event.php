<?php

namespace App;

use Illuminate\Support\Facades\Redis;

class Event
{
  public static function create($client_type, $event) {
    $clients = \App\Client::where('client_type', '=', $client_type)->get();
    foreach ($clients as $client) {
      // store in Redis
      Redis::rpush('session:'.$client->session_id, $event);
    }
  }

  public static function get($session_id) {
    $event = Redis::lpop('session:'.$session_id);
    if ($event) {
      return "data: {$event}\n\n";
    }
  }
}
