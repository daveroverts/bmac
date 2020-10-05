<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\User\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Faq\FaqAdminController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Event\EventAdminController;
use App\Http\Controllers\Airport\AirportAdminController;
use App\Http\Controllers\Booking\BookingAdminController;
use App\Http\Controllers\AirportLink\AirportLinkAdminController;

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

Route::get('/', HomeController::class)->name('home');

Route::get('/login', [LoginController::class, 'login'])->name('login');
Route::get('/validate-login', [LoginController::class, 'validateLogin']);
Route::get('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => 'auth.isAdmin'], function () {
    // Airports
    Route::get('airports/import', [AirportAdminController::class, 'import'])->name('admin.airports.import');
    Route::resource('airports', AirportAdminController::class);

    // AirportLinks
    Route::resource('airportLinks', AirportLinkAdminController::class)->except(['show']);

    // Faq
    Route::resource('faq', FaqAdminController::class)->except('show');
    Route::patch('faq/{faq}/toggle-event/{event}', [FaqAdminController::class, 'toggleEvent'])->name('faq.toggleEvent');

    // Event
    Route::resource('events', EventAdminController::class);
    Route::get('{event}/email', [EventAdminController::class, 'sendEmailForm'])->name('events.email.form');
    Route::patch('{event}/email', [EventAdminController::class, 'sendEmail'])->name('events.email');
    Route::patch(
        '{event}/email_final',
        [EventAdminController::class, 'sendFinalInformationMail']
    )->name('events.email.final');

    // Booking
    Route::resource('bookings', BookingAdminController::class)->except(['index', 'show']);
    Route::get('{event}/bookings/export/{vacc?}', [BookingAdminController::class, 'export'])->name('bookings.export');
    Route::get('{event}/bookings/create/{bulk?}', [BookingAdminController::class, 'create'])->name('bookings.create');
    Route::get('{event}/bookings/import', [BookingAdminController::class, 'importForm'])->name('bookings.importForm');
    Route::put('{event}/bookings/import', [BookingAdminController::class, 'import'])->name('bookings.import');
    Route::get(
        '{event}/bookings/auto-assign',
        [BookingAdminController::class, 'adminAutoAssignForm']
    )->name('bookings.autoAssignForm');
    Route::patch(
        '{event}/bookings/auto-assign',
        [BookingAdminController::class, 'adminAutoAssign']
    )->name('bookings.autoAssign');
    Route::get(
        '{event}/bookings/route-assign',
        [BookingAdminController::class, 'routeAssignForm']
    )->name('bookings.routeAssignForm');
    Route::patch(
        '{event}/bookings/route-assign',
        [BookingAdminController::class, 'routeAssign']
    )->name('bookings.routeAssign');
});

Route::resource('bookings', BookingController::class)->except(['create', 'edit', 'store', 'destroy']);
Route::get('/{event}/bookings/{filter?}', [BookingController::class, 'index'])->name('bookings.event.index');
Route::get('/bookings/{booking}/edit', [BookingController::class, 'edit'])
    ->middleware('auth.isLoggedIn')->name('bookings.edit');
Route::patch('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');

// Keeping this here for a while to prevent some 404's should people access /booking directly
Route::redirect('/booking', route('bookings.index'), 301);

Route::get('faq', FaqController::class)->name('faq');

Route::get('{event}', EventController::class)->name('events.show');

Route::middleware('auth.isLoggedIn')->group(function () {
    Route::prefix('user')->name('user.')
        ->group(function () {
            Route::get('settings', [UserController::class, 'showSettingsForm'])->name('settings');
            Route::patch('settings', [UserController::class, 'saveSettings'])->name('saveSettings');
        });
});
