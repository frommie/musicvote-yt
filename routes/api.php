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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/playcontrol', 'PlaylistController@control');

Route::get('/votes', 'VoteController@get');

Route::get('/next', 'PlaylistController@next');

Route::get('/playlist', 'PlaylistController@playlist');

Route::post('/search', 'BackendController@search');

Route::post('/vote/{id}', 'ItemController@vote');
