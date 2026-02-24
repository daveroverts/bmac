<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\BookingStatus;
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
        return new BookingsCollection(
            $event->bookings()
                ->where('status', BookingStatus::BOOKED)
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
