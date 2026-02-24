<?php

use App\Enums\BookingStatus;
use App\Http\Controllers\Api\V1;
use App\Http\Middleware\DeprecatedApiMiddleware;
use App\Http\Resources\AirportResource;
use App\Http\Resources\AirportsCollection;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingsCollection;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventsCollection;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// V1 — versioned, stable
Route::prefix('v1')->name('v1.')->group(function (): void {
    // Events
    Route::get('events/upcoming/{limit?}', [V1\EventController::class, 'upcoming'])
        ->name('events.upcoming');
    Route::apiResource('events', V1\EventController::class)->only(['index', 'show']);

    // Bookings
    Route::get('events/{event}/bookings', [V1\BookingController::class, 'byEvent'])
        ->name('events.bookings.index');
    Route::apiResource('bookings', V1\BookingController::class)->only(['show']);

    // Airports
    Route::apiResource('airports', V1\AirportController::class)->only(['index', 'show']);
});

// Legacy unversioned routes — deprecated, will be removed 2026-12-31
Route::middleware(DeprecatedApiMiddleware::class)->group(function (): void {
    Route::get('/events/upcoming/{limit?}', fn ($limit = 3): EventsCollection => new EventsCollection(Event::where('is_online', true)
        ->where('endEvent', '>', now())
        ->orderBy('startEvent', 'asc')
        ->limit($limit)
        ->get()));

    Route::get('/events/{event}/bookings', fn (Event $event): BookingsCollection => new BookingsCollection(
        $event->bookings()
            ->where('status', BookingStatus::BOOKED)
            ->with(['flights.airportDep', 'flights.airportArr', 'user', 'event'])
            ->get()
    ));

    Route::get('/events/{event}', fn (Event $event): EventResource => new EventResource($event));

    Route::get('/events', fn (): EventsCollection => new EventsCollection(Event::paginate()));

    Route::get('/bookings/{booking}', function (Booking $booking): BookingResource {
        $booking->loadMissing(['flights.airportDep', 'flights.airportArr', 'user', 'event']);

        return new BookingResource($booking);
    });

    Route::get('/airports/{airport}', fn (Airport $airport): AirportResource => new AirportResource($airport));

    Route::get('/airports', fn (): AirportsCollection => new AirportsCollection(Airport::paginate()));
});
