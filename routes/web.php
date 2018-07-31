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
    return view('home');
});

Route::get('/login', 'Auth\LoginController@login')->name('login');
Route::get('/validateLogin', 'Auth\LoginController@validateLogin');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::resource('admin/airport', 'AirportController');

Route::get('/admin/event/{event}/email', 'EventController@sendEmailForm')->name('event.email.form');
Route::patch('/admin/event/{event}/email', 'EventController@sendEmail')->name('event.email');
Route::get('/admin/event/{event}/email_final', 'EventController@sendFinalInformationMail')->name('event.email.final');
Route::resource('admin/event', 'EventController');

Route::put('booking/{booking}', 'BookingController@cancel')->name('booking.cancel');
Route::get('/booking/{id}/create', 'BookingController@create')->name('booking.create');
Route::get('/booking/{id}/export', 'BookingController@export')->name('booking.export');
Route::get('/admin/booking/{booking}/edit', 'BookingController@adminEdit')->name('booking.admin.edit');
Route::patch('/admin/booking/{booking}/edit', 'BookingController@adminUpdate')->name('booking.admin.update');
Route::get('/admin/booking/{id}/autoAssign', 'BookingController@adminAutoAssignForm')->name('booking.admin.autoAssignForm');
Route::patch('/admin/booking/{id}/autoAssign', 'BookingController@adminAutoAssign')->name('booking.admin.autoAssign');
Route::resource('booking', 'BookingController')->except('create');

Route::get('/faq', function () {
    return view('faq');
})->name('faq');
