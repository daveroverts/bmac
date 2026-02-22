<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Faq\FaqAdminController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Event\EventAdminController;
use App\Http\Controllers\Event\EventEmailController;
use App\Http\Controllers\Airport\AirportAdminController;
use App\Http\Controllers\Booking\BookingAdminController;
use App\Http\Controllers\Booking\BookingExportController;
use App\Http\Controllers\Booking\BookingImportController;
use App\Http\Controllers\Booking\BookingAutoAssignController;
use App\Http\Controllers\Booking\BookingRouteAssignController;
use App\Http\Controllers\Booking\BookingCancellationController;
use App\Http\Controllers\Booking\BookingReservationController;
use App\Http\Controllers\AirportLink\AirportLinkAdminController;
use App\Http\Controllers\EventLink\EventLinkAdminController;
use App\Http\Controllers\User\UserSettingsController;

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
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::group(['as' => 'admin.', 'prefix' => 'admin', 'middleware' => 'auth.isAdmin'], function (): void {
    // Airports
    Route::delete('airports/unused', [AirportAdminController::class, 'destroyUnused'])->name('airports.unused.destroy');
    Route::resource('airports', AirportAdminController::class);

    // AirportLinks
    Route::resource('airportLinks', AirportLinkAdminController::class)->except(['show']);

    // EventLinks
    Route::resource('eventLinks', EventLinkAdminController::class)->except(['show']);

    // Faq
    Route::resource('faq', FaqAdminController::class)->except('show');
    Route::patch('faq/{faq}/toggle-event/{event}', [FaqAdminController::class, 'toggleEvent'])->name('faq.toggleEvent');

    // Event
    Route::resource('events', EventAdminController::class);
    Route::get('events/{event}/emails/bulk', [EventEmailController::class, 'createBulk'])->name('events.emails.bulk.create');
    Route::post('events/{event}/emails/bulk', [EventEmailController::class, 'sendBulk'])->name('events.emails.bulk.send');
    Route::post('events/{event}/emails/final', [EventEmailController::class, 'sendFinal'])->name('events.emails.final.send');
    Route::delete('events/{event}/bookings', [BookingAdminController::class, 'destroyAll'])->name('events.bookings.destroyAll');

    // Booking
    Route::resource('bookings', BookingAdminController::class)->except(['index', 'create', 'show']);
    Route::get('events/{event}/bookings/export', BookingExportController::class)->name('events.bookings.export');
    Route::get('{event}/bookings/create', [BookingAdminController::class, 'create'])->name('bookings.create');
    Route::get('{event}/bookings/import', [BookingImportController::class, 'create'])->name('bookings.import.create');
    Route::post('{event}/bookings/import', [BookingImportController::class, 'store'])->name('bookings.import.store');
    Route::get('{event}/bookings/auto-assign', [BookingAutoAssignController::class, 'create'])->name('bookings.autoAssign.create');
    Route::post('{event}/bookings/auto-assign', [BookingAutoAssignController::class, 'store'])->name('bookings.autoAssign.store');
    Route::get('{event}/bookings/route-assign', [BookingRouteAssignController::class, 'create'])->name('bookings.routeAssign.create');
    Route::post('{event}/bookings/route-assign', [BookingRouteAssignController::class, 'store'])->name('bookings.routeAssign.store');
});

Route::resource('bookings', BookingController::class)
    ->only(['show', 'edit', 'update'])
    ->middleware(['edit' => 'auth.isLoggedIn']);
Route::post('bookings/{booking}/reservation', [BookingReservationController::class, 'store'])
    ->middleware('auth.isLoggedIn')
    ->name('bookings.reservation.store');
Route::delete('bookings/{booking}/cancellation', [BookingCancellationController::class, 'destroy'])
    ->middleware('auth.isLoggedIn')
    ->name('bookings.cancellation.destroy');
Route::get('/{event}/bookings/{filter?}', [BookingController::class, 'index'])->name('bookings.event.index');

Route::get('faq', FaqController::class)->name('faq');

Route::get('{event}', EventController::class)->name('events.show');

Route::middleware('auth.isLoggedIn')->prefix('user')->name('user.')->group(function (): void {
    Route::singleton('settings', UserSettingsController::class)->only(['edit', 'update']);
});
