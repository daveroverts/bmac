<?php

namespace App\Http\Controllers\Booking;

use App\Models\Event;
use App\Models\Booking;
use App\Enums\EventType;
use Illuminate\Support\Str;
use App\Enums\BookingStatus;
use Illuminate\Http\Request;
use App\Events\BookingConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\UpdateBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request, Event $event): View|RedirectResponse
    {
        return view('booking.overview', ['event' => $event]);
    }

    public function show(Booking $booking): View
    {
        if ($booking->event->event_type_id == EventType::MULTIFLIGHTS->value) {
            return view('booking.show_multiflights', ['booking' => $booking]);
        }

        $flight = $booking->flights->first();
        return view('booking.show', ['booking' => $booking, 'flight' => $flight]);
    }

    public function edit(Booking $booking): View|RedirectResponse
    {
        $this->authorize('edit', $booking);

        // Check booking window (hard lock after endBooking)
        if ($booking->event->endBooking < now()) {
            flashMessage(
                'danger',
                __('Danger'),
                __('Bookings have been locked at :time', [
                    'time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z'
                ])
            );
            return to_route('bookings.event.index', $booking->event);
        }

        // Check if editable for BOOKED status
        if ($booking->status === BookingStatus::BOOKED && !$booking->is_editable) {
            flashMessage('info', __('Danger'), __('You cannot edit the booking!'));
            return to_route('bookings.event.index', $booking->event);
        }

        // Show edit form
        if ($booking->event->event_type_id == EventType::MULTIFLIGHTS->value) {
            return view('booking.edit_multiflights', ['booking' => $booking]);
        }

        $flight = $booking->flights->first();
        return view('booking.edit', ['booking' => $booking, 'flight' => $flight]);
    }

    public function update(UpdateBooking $request, Booking $booking): RedirectResponse
    {
        // This check should actually be in the policy, but is now here as a quick fix
        if ($booking->user_id === $request->user()->id) {
            if ($booking->is_editable) {
                $booking->fill([
                    'callsign' => $request->callsign,
                    'acType' => $request->acType
                ]);
            }

            if ($booking->event->is_oceanic_event && $request->filled('selcal')) {
                $booking->selcal = $request->selcal;
            }

            if ($booking->status == BookingStatus::RESERVED) {
                $booking->status = BookingStatus::BOOKED;
                $booking->save();
                event(new BookingConfirmed($booking));
                if (!Str::contains(config('mail.default'), ['log', 'array'])) {
                    $message = __('Your booking has been confirmed. You should shortly receive an email with your booking details. Be sure to also check your spam folder.');
                } else {
                    $message = __('Your booking has been confirmed');
                }

                flashMessage(
                    'success',
                    __('Booking confirmed!'),
                    $message
                );
            } else {
                $booking->save();
                flashMessage('success', __('Booking edited!'), __('Booking has been edited!'));
            }

            return to_route('bookings.event.index', $booking->event);
        }

        abort(403);
    }

}
