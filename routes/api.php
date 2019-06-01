<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('user', function (Request $request) {
    return $request->user();
});

Route::get('playcontrol', 'BackendController@control');

Route::get('votes', 'VoteController@get');

Route::get('first', 'PlayController@first');

Route::get('next', 'PlayController@next');

Route::get('playlist', 'PlaylistController@playlist');

Route::post('search', 'BackendController@search');

Route::prefix('vote')->group(function () {
  Route::post('up/{id}', 'VoteController@upvote');
  Route::post('down/{id}', 'VoteController@downvote');
});

Route::get('/load/fallback', 'PlayController@load_fallback_playlist');

Route::prefix('spotify')->group(function () {
  Route::get('auth', 'BackendController@auth');
  Route::get('cb', 'BackendController@callback')->name('spotify.cb');
  Route::get('test', 'BackendController@test');
});
