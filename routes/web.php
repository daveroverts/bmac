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

Route::get('/', 'HomeController')->name('home');

Route::get('/login/{booking?}', 'Auth\LoginController@login')->name('login');
Route::get('/validateLogin', 'Auth\LoginController@validateLogin');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

// Admin routes
Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => 'auth.isAdmin'], function () {
    // Airports
    Route::get('airports/import', 'Airport\AirportAdminController@import')->name('admin.airports.import');
    Route::resource('airports', 'Airport\AirportAdminController');

    // AirportLinks
    Route::resource('airportLinks', 'AirportLink\AirportLinkAdminController')->except(['show']);

    // Faq
    Route::resource('faq', 'Faq\FaqAdminController')->except('show');
    Route::patch('faq/{faq}/toggle-event/{event}', 'Faq\FaqAdminController@toggleEvent')->name('faq.toggleEvent');

    // Event
    Route::resource('events', 'Event\EventAdminController');
    Route::get('{event}/email', 'Event\EventAdminController@sendEmailForm')->name('events.email.form');
    Route::patch('{event}/email', 'Event\EventAdminController@sendEmail')->name('events.email');
    Route::patch('{event}/email_final',
        'Event\EventAdminController@sendFinalInformationMail')->name('events.email.final');

    // Booking
    Route::resource('bookings', 'Booking\BookingAdminController')->except(['index', 'show']);
    Route::get('{event}/bookings/export/{vacc?}', 'Booking\BookingAdminController@export')->name('bookings.export');
    Route::get('{event}/bookings/create/{bulk?}', 'Booking\BookingAdminController@create')->name('bookings.create');
    Route::get('{event}/bookings/import', 'Booking\BookingAdminController@importForm')->name('bookings.importForm');
    Route::put('{event}/bookings/import', 'Booking\BookingAdminController@import')->name('bookings.import');
    Route::get('{event}/bookings/autoAssign',
        'Booking\BookingAdminController@adminAutoAssignForm')->name('bookings.autoAssignForm');
    Route::patch('{event}/bookings/autoAssign',
        'Booking\BookingAdminController@adminAutoAssign')->name('bookings.autoAssign');
});

Route::resource('bookings', 'Booking\BookingController')->except(['create', 'store', 'destroy']);
Route::get('/{event}/bookings/{filter?}', 'Booking\BookingController@index')->name('bookings.event.index');
Route::patch('/bookings/{booking}/cancel', 'Booking\BookingController@cancel')->name('bookings.cancel');

// Keeping this here for a while to prevent some 404's should people access /booking directly
Route::redirect('/booking', route('bookings.index'), 301);

Route::get('faq', 'Faq\FaqController')->name('faq');

Route::get('{event}', 'Event\EventController')->name('events.show');

Route::middleware('auth.isLoggedIn')->group(function () {
    Route::prefix('user')->name('user.')
        ->group(function () {
            Route::get('settings', 'User\UserController@showSettingsForm')->name('settings');
            Route::patch('settings', 'User\UserController@saveSettings')->name('saveSettings');
        });
});
