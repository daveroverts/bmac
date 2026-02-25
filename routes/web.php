<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\Faq\FaqController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Event\EventController;
use App\Http\Controllers\Faq\FaqAdminController;
use App\Http\Controllers\Faq\FaqEventController;
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

Route::middleware('guest')->get('/login', [LoginController::class, 'login'])->name('login');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Admin routes
Route::middleware(['auth', 'can:admin'])->prefix('admin')->name('admin.')->group(function (): void {
    // Airports
    Route::delete('airports/unused', [AirportAdminController::class, 'destroyUnused'])->name('airports.unused.destroy');
    Route::resource('airports', AirportAdminController::class);

    // AirportLinks
    Route::resource('airportLinks', AirportLinkAdminController::class)->except(['show']);

    // EventLinks
    Route::resource('eventLinks', EventLinkAdminController::class)->except(['show']);

    // Faq
    Route::resource('faq', FaqAdminController::class)->except('show');
    Route::post('faq/{faq}/events/{event}', [FaqEventController::class, 'store'])->name('faq.events.attach');
    Route::delete('faq/{faq}/events/{event}', [FaqEventController::class, 'destroy'])->name('faq.events.detach');

    // Events
    Route::resource('events', EventAdminController::class);

    // Event-scoped routes
    Route::prefix('events/{event}')->name('events.')->group(function (): void {
        // Event emails
        Route::prefix('emails')->name('emails.')->group(function (): void {
            Route::get('bulk', [EventEmailController::class, 'createBulk'])->name('bulk.create');
            Route::post('bulk', [EventEmailController::class, 'sendBulk'])->name('bulk.send');
            Route::post('final', [EventEmailController::class, 'sendFinal'])->name('final.send');
        });

        // Event bookings
        Route::delete('bookings', [BookingAdminController::class, 'destroyAll'])->name('bookings.destroyAll');

        Route::prefix('bookings')->name('bookings.')->group(function (): void {
            Route::get('create', [BookingAdminController::class, 'create'])->name('create');
            Route::get('export', BookingExportController::class)->name('export');

            Route::get('import', [BookingImportController::class, 'create'])->name('import.create');
            Route::post('import', [BookingImportController::class, 'store'])->name('import.store');

            Route::get('auto-assign', [BookingAutoAssignController::class, 'create'])->name('autoAssign.create');
            Route::post('auto-assign', [BookingAutoAssignController::class, 'store'])->name('autoAssign.store');

            Route::get('route-assign', [BookingRouteAssignController::class, 'create'])->name('routeAssign.create');
            Route::post('route-assign', [BookingRouteAssignController::class, 'store'])->name('routeAssign.store');
        });
    });

    // Booking resource (not event-scoped)
    Route::resource('bookings', BookingAdminController::class)->except(['index', 'create', 'show']);
});

Route::resource('bookings', BookingController::class)
    ->only(['show', 'edit', 'update'])
    ->middleware('auth');
Route::post('bookings/{booking}/reservation', [BookingReservationController::class, 'store'])
    ->middleware('auth')
    ->name('bookings.reservation.store');
Route::delete('bookings/{booking}/cancellation', [BookingCancellationController::class, 'destroy'])
    ->middleware('auth')
    ->name('bookings.cancellation.destroy');
Route::get('events/{event}/bookings', [BookingController::class, 'index'])->name('events.bookings.index');

Route::get('faq', FaqController::class)->name('faq');

Route::get('{event}', EventController::class)->name('events.show');

Route::middleware('auth')->prefix('user')->name('user.')->group(function (): void {
    Route::singleton('settings', UserSettingsController::class)->only(['edit', 'update']);
});
