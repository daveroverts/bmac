<?php

namespace App\Http\Controllers\Booking;

use App\Enums\BookingStatus;
use App\Events\BookingCancelled;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\RedirectResponse;

class BookingCancellationController extends Controller
{
    public function destroy(Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);

        if ($booking->event->endBooking < now()) {
            flashMessage(
                'danger',
                __('Danger'),
                __('Bookings have been locked at :time', ['time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z'])
            );

            return to_route('events.bookings.index', $booking->event);
        }

        if ($booking->is_editable) {
            $booking->fill([
                'callsign' => null,
                'acType' => null,
            ]);
        }

        $booking->selcal = null;

        if ($booking->status === BookingStatus::BOOKED) {
            event(new BookingCancelled($booking, auth()->user()));
            $title = __('Booking cancelled!');
            $message = __('Booking has been cancelled!');
        } else {
            $title = __('Slot free');
            $message = __('Slot is now free to use again');
        }

        $booking->status = BookingStatus::UNASSIGNED;
        $booking->user()->dissociate()->save();

        flashMessage('info', $title, $message);

        return to_route('events.bookings.index', $booking->event);
    }
}
