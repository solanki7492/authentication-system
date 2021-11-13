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
Route::prefix('user')->group(function () {
    Route::post('/send-invitation', "UserController@sendInvitiation")->name("api.user.send-invitation");
    Route::post('/create', "UserController@create")->name("api.user.create");
    Route::post('/login', "UserController@login")->name("api.user.login");
});

Route::middleware('auth:api')->prefix('user')->group(function () {
    Route::post('/update', "UserController@update")->name("api.user.update");
});


