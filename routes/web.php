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
    $event = \App\Models\Event::where('endEvent', '>', now())->orderBy('startEvent', 'desc')->first();
    return view('home', compact('event'));
})->name('home');

Route::get('/login', 'Auth\LoginController@login')->name('login');
Route::get('/validateLogin', 'Auth\LoginController@validateLogin');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/admin/airports/import', 'AirportController@import')->name('airports.import');
Route::resource('admin/airports', 'AirportController');

Route::resource('admin/airportLinks', 'AirportLinkController')->except(['show']);

Route::resource('admin/events', 'EventController');
Route::get('/admin/events/{event}/email', 'EventController@sendEmailForm')->name('events.email.form');
Route::patch('/admin/events/{event}/email', 'EventController@sendEmail')->name('events.email');
Route::get('/admin/events/{event}/email_final', 'EventController@sendFinalInformationMail')->name('events.email.final');

Route::resource('bookings', 'BookingController')->except(['index', 'create']);
Route::get('/bookings', 'BookingController@index')->name('bookings.index');
Route::get('/{event}/bookings/export', 'BookingController@export')->name('bookings.export');
Route::get('/{event}/bookings/{filter?}', 'BookingController@index')->name('bookings.event.index');
Route::get('/{event}/bookings/create/{bulk?}', 'BookingController@create')->name('bookings.create');
Route::get('/bookings/{booking}/cancel', 'BookingController@cancel')->name('bookings.cancel');
Route::get('/admin/{event}/bookings/import', 'BookingController@importForm')->name('bookings.admin.importForm');
Route::put('/admin/{event}/bookings/import', 'BookingController@import')->name('bookings.admin.import');
Route::get('/admin/bookings/{booking}/edit', 'BookingController@adminEdit')->name('bookings.admin.edit');
Route::patch('/admin/bookings/{booking}/edit', 'BookingController@adminUpdate')->name('bookings.admin.update');
Route::get('/admin/{event}/bookings/autoAssign', 'BookingController@adminAutoAssignForm')->name('bookings.admin.autoAssignForm');
Route::patch('/admin/{event}/bookings/autoAssign', 'BookingController@adminAutoAssign')->name('bookings.admin.autoAssign');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');
