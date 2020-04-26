<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('throttle:5,1')->post('login', 'User\AuthController@login');
Route::group(['middleware' => 'auth:api'], function() {
    Route::post('logout', 'User\AuthController@logout');
    Route::apiResource('users', 'User\UserController', ['only' => ['index', 'show']]);
});

