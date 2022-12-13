<?php

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

Route::group(['namespace' => 'Api'], function () {

  Route::get('get_token', 'UserController@getToken');

  Route::resource('users', 'UsersController');

  Route::get('count_except', 'ProblemsController@getCountExcept');
  Route::get('string_index', 'ProblemsController@getStringIndex');
  Route::get('minmum_down_to_zero', 'ProblemsController@getMinmumStepsToZero');




  Route::group(['middleware' => 'jwt.auth:user'], function () {

    Route::get('profile', 'UserController@getUser');
    Route::put('profile', 'UserController@updateUser');
  });

  Route::group(['namespace' => 'Auth'], function () {

    Route::post('register', 'RegisterController@register');
    Route::post('login', 'LoginController@login');
  });
});
