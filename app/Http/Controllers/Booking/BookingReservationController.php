<?php

namespace App\Http\Controllers\Booking;

use App\Models\Booking;
use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;

class BookingReservationController extends Controller
{
    public function store(Booking $booking): RedirectResponse
    {
        $this->authorize('reserve', $booking);

        // Check if booking window is still open
        if ($booking->event->endBooking < now()) {
            flashMessage('danger', __('Danger'), __('Bookings have been closed at :time', ['time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z']));
            return to_route('events.bookings.index', $booking->event);
        }

        // Check if user already has a reservation
        if (auth()->user()->bookings()
            ->where('event_id', $booking->event_id)
            ->reserved()
            ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a reservation! Please cancel or book that flight first.'));
            return to_route('events.bookings.index', $booking->event);
        }

        // Check if event allows multiple bookings
        if (!$booking->event->multiple_bookings_allowed
            && auth()->user()->bookings()
                ->where('event_id', $booking->event_id)
                ->booked()
                ->exists()) {
            flashMessage('danger', __('Warning'), __('You already have a booking!'));
            return to_route('events.bookings.index', $booking->event);
        }

        // Atomically claim the slot: only update if it is still UNASSIGNED.
        // This prevents two simultaneous requests from both passing the policy
        // check and both reserving the same slot.
        $claimed = Booking::query()
            ->where('id', $booking->id)
            ->unassigned()
            ->update([
                'status' => BookingStatus::RESERVED,
                'user_id' => auth()->id(),
            ]);

        if ($claimed === 0) {
            flashMessage('danger', __('Warning'), __('Whoops! Somebody else reserved that slot just before you! Please choose another one.'));
            return to_route('events.bookings.index', $booking->event);
        }

        $booking->refresh();

        activity()
            ->by(auth()->user())
            ->on($booking)
            ->log('Flight reserved');

        flashMessage(
            'info',
            __('Slot reserved'),
            __('Slot remains reserved until :time', [
                'time' => $booking->updated_at->addMinutes(Booking::RESERVATION_TIMEOUT_MINUTES)->format('Hi') . 'z'
            ])
        );

        return to_route('bookings.edit', $booking);
    }
}
