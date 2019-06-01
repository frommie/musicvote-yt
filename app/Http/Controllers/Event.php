<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Redis;

class Event
{
  public static function create($client_type, $event) {
    $clients = \App\Client::where('client_type', '=', $client_type)->get();
    foreach ($clients as $client) {
      // store in Redis
      Redis::sadd('event:'.$client->session_id, $event);
    }
  }

  public static function get($session_id) {
    $event = Redis::spop('event:'.$session_id);
    if ($event) {
      return $event;
    } else {
      return;
    }
  }
}
