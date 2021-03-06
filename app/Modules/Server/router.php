<?php

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/* 默认 入口*/
Route::any('/', "IndexController@index");

/* index - Index */
Route::any('index/index', "IndexController@index");

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/

/* auth - getMemberList */
Route::any('auth/loginByEmail', "AuthController@loginByEmail");

/* auth - resetPwdByOld */
Route::any('auth/resetPwdByOld', "AuthController@resetPwdByOld");

/* auth - logout */
Route::any('auth/logout', "AuthController@logout");

/* auth - info */
Route::any('auth/info', "AuthController@info");

/* auth - wxLogin */
Route::any('auth/wxLogin', "AuthController@wxLogin");

/* auth - wxInfo */
Route::any('auth/wxInfo', "AuthController@wxInfo");





