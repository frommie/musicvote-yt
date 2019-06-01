<?php

namespace App\Http\Middleware;

use Closure;
use Session;
use Illuminate\Support\Facades\Redis;

class Heartbeat
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next) {
      $session_id = Session::getId();
      Redis::set('activity:'.$session_id, time(), 'EX', 3600);
      return $next($request);
    }
}
