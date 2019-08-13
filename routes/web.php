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
    $events = nextEvents(false, false, true);
    return view('home', compact('events'));
})->name('home');

Route::get('/login/{booking?}', 'Auth\LoginController@login')->name('login');
Route::get('/validateLogin', 'Auth\LoginController@validateLogin');
Route::get('/logout', 'Auth\LoginController@logout')->name('logout');

Route::get('/admin/airports/import', 'AirportController@import')->name('airports.import');
Route::resource('admin/airports', 'AirportController');

Route::resource('admin/airportLinks', 'AirportLinkController')->except(['show']);

Route::resource('admin/events', 'EventController')->except(['show']);
Route::get('/admin/events/{event}', 'EventController@adminShow')->name('events.admin.show');
Route::get('/admin/events/{event}/email', 'EventController@sendEmailForm')->name('events.email.form');
Route::patch('/admin/events/{event}/email', 'EventController@sendEmail')->name('events.email');
Route::patch('/admin/events/{event}/email_final', 'EventController@sendFinalInformationMail')->name('events.email.final');

Route::resource('bookings', 'BookingController')->except(['create']);
Route::get('/{event}/bookings/export', 'BookingController@export')->name('bookings.export');
Route::get('/{event}/bookings/create/{bulk?}', 'BookingController@create')->name('bookings.create');
Route::get('/{event}/bookings/{filter?}', 'BookingController@index')->name('bookings.event.index');
Route::patch('/bookings/{booking}/cancel', 'BookingController@cancel')->name('bookings.cancel');
Route::get('/admin/{event}/bookings/import', 'BookingController@importForm')->name('bookings.admin.importForm');
Route::put('/admin/{event}/bookings/import', 'BookingController@import')->name('bookings.admin.import');
Route::get('/admin/bookings/{booking}/edit', 'BookingController@adminEdit')->name('bookings.admin.edit');
Route::patch('/admin/bookings/{booking}/edit', 'BookingController@adminUpdate')->name('bookings.admin.update');
Route::get('/admin/{event}/bookings/autoAssign', 'BookingController@adminAutoAssignForm')->name('bookings.admin.autoAssignForm');
Route::patch('/admin/{event}/bookings/autoAssign', 'BookingController@adminAutoAssign')->name('bookings.admin.autoAssign');

// Keeping this here for a while to prevent some 404's should people access /booking directly
Route::redirect('/booking', route('bookings.index'), 301);

Route::get('/faq', function () {
    $faqs = \App\Models\Faq::doesntHave('events')
    ->where('is_online', '=' , '1')
    ->get();

    return view('faq', compact('faqs'));
})->name('faq');

Route::resource('admin/faq', 'FaqController')->except('show');
Route::patch('/admin/faq/{faq}/toggle-event/{event}', 'FaqController@toggleEvent')->name('faq.toggleEvent');

Route::get('/{event}', 'EventController@show')->name('events.show');

Route::middleware('auth.isLoggedIn')->group(function () {
    Route::prefix('user')->name('user.')
        ->group(function () {
        Route::get('settings', function () {
            $user = Auth::user();
            return view('user.settings', compact('user'));
        })->name('settings');

        Route::patch('settings', function (\App\Http\Requests\UpdateUserSettings $request) {
            $user = Auth::user();
            $user->update($request->only(['airport_view', 'use_monospace_font']));
            flashMessage('success', 'Done', 'Settings saved!');
            return back();
        })->name('saveSettings');
    });
});
