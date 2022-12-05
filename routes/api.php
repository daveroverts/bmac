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

Route::get('/events/upcoming/{limit?}', function ($limit = 3) {
    return new EventsCollection(Event::where('is_online', true)
        ->where('endEvent', '>', now())
        ->orderBy('startEvent', 'asc')
        ->limit($limit)
        ->get());
});

Route::get('/events/{event}/bookings', function (Event $event) {
    return new BookingsCollection($event->bookings->where('status', BookingStatus::BOOKED));
});

Route::get('/events/{event}', function (Event $event) {
    return new EventResource($event);
});

Route::get('/events', function () {
    return new EventsCollection(Event::paginate());
});

Route::get('/bookings/{booking}', function (Booking $booking) {
    return new BookingResource($booking);
});

Route::get('/airports/{airport}', function (Airport $airport) {
    return new AirportResource($airport);
});

Route::get('/airports', function () {
    return new AirportsCollection(Airport::paginate());
});
