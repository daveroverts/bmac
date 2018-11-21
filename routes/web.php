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

Route::resource('booking', 'BookingController')->except(['index', 'create']);
Route::get('/bookings/{event?}/{filter?}', 'BookingController@index')->name('booking.index');
Route::get('/booking/{event}/create/{bulk?}', 'BookingController@create')->name('booking.create');
Route::get('/booking/{booking}/cancel', 'BookingController@cancel')->name('booking.cancel');
Route::get('/booking/{event}/export', 'BookingController@export')->name('booking.export');
Route::get('/admin/booking/{event}/import', 'BookingController@importForm')->name('booking.admin.importForm');
Route::put('/admin/booking/{event}/import', 'BookingController@import')->name('booking.admin.import');
Route::get('/admin/booking/{booking}/edit', 'BookingController@adminEdit')->name('booking.admin.edit');
Route::patch('/admin/booking/{booking}/edit', 'BookingController@adminUpdate')->name('booking.admin.update');
Route::get('/admin/booking/{event}/autoAssign', 'BookingController@adminAutoAssignForm')->name('booking.admin.autoAssignForm');
Route::patch('/admin/booking/{event}/autoAssign', 'BookingController@adminAutoAssign')->name('booking.admin.autoAssign');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');
