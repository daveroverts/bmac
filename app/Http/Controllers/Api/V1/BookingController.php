<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BookingResource;
use App\Http\Resources\BookingsCollection;
use App\Models\Booking;
use App\Models\Event;

class BookingController extends Controller
{
    /**
     * Return all confirmed bookings for a given event.
     */
    public function byEvent(Event $event): BookingsCollection
    {
        abort_unless($event->is_online, 404);

        return new BookingsCollection(
            $event->bookings()
                ->booked()
                ->with(['flights.airportDep', 'flights.airportArr', 'user', 'event'])
                ->get()
        );
    }

    /**
     * Return a single booking.
     */
    public function show(Booking $booking): BookingResource
    {
        $booking->loadMissing(['flights.airportDep', 'flights.airportArr', 'user', 'event']);

        return new BookingResource($booking);
    }
}
