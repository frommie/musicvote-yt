<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class StoreSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
      $client = \App\Client::firstOrNew(['session_id' => Session::getId()]);

      if ($request->path() == 'play') {
        // register player
        $client->client_type = 'player';
      } else {
        // register client
        $client->client_type = 'client';
      }
      $client->save();

      return $next($request);
    }
}
