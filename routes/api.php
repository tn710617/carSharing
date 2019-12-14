<?php

use App\Http\Controllers\UsersController;
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
Route::post('login', 'AuthController@login');
Route::apiResource('register', 'UsersController')->only(['store']);
Route::middleware('auth:api')->group(function() {
    Route::post('logout', 'AuthController@logout');
    Route::apiResource('post', 'PostController')->only(['store']);
});



