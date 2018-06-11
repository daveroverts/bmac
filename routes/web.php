<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login','Auth\LoginController@login');
Route::get('/validateLogin','Auth\LoginController@validateLogin');

Route::resource('admin/airport', 'AirportController');
Route::resource('admin/event', 'EventController');
Route::get('/booking/{id}/create','BookingController@create')->name('booking.create');
Route::resource('booking', 'BookingController')->except('create');

