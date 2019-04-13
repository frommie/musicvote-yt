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

Route::get('/playcontrol', function () {
    return 'Hello World';
});

Route::get('/get_user_votes', function () {
    return 'Hello World';
});

Route::get('/next', function () {
    return 'Hello World';
});

Route::get('/playlist', function () {
    return 'Hello World';
});

Route::post('/search', function () {
    return 'Hello World';
});

Route::post('/vote', function (Request $request) {
    return 'Hello World';
});
