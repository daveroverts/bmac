<?php

use App\Enums\BookingStatus;
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

// Route::get('/users/{user}', function (User $user) {
//     return new UserResource($user);
// });

// Route::get('/users', function () {
//     return new UsersCollection(User::all());
// });

Route::get('/events/upcoming/{limit?}', fn ($limit = 3) => new EventsCollection(Event::where('is_online', true)
    ->where('endEvent', '>', now())
    ->orderBy('startEvent', 'asc')
    ->limit($limit)
    ->get()));

Route::get('/events/{event}/bookings', fn (Event $event) => new BookingsCollection($event->bookings->where('status', BookingStatus::BOOKED->value)));

Route::get('/events/{event}', fn (Event $event) => new EventResource($event));

Route::get('/events', fn () => new EventsCollection(Event::paginate()));

Route::get('/bookings/{booking}', fn (Booking $booking) => new BookingResource($booking));

Route::get('/airports/{airport}', fn (Airport $airport) => new AirportResource($airport));

Route::get('/airports', fn () => new AirportsCollection(Airport::paginate()));
