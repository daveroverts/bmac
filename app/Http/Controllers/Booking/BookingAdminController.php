<?php

namespace App\Http\Controllers\Booking;

use App\Actions\Booking\CreateBookingsAction;
use App\Actions\Booking\UpdateBookingAction;
use App\Events\BookingDeleted;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Http\Requests\Booking\Admin\UpdateBooking;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingAdminController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Booking::class, 'booking');
    }

    public function create(Event $event, Request $request): View
    {
        $bulk = $request->bulk;
        $airports = Airport::forDropdown();

        return view('booking.admin.create', ['event' => $event, 'airports' => $airports, 'bulk' => $bulk]);
    }

    public function store(StoreBooking $request, CreateBookingsAction $action): RedirectResponse
    {
        $event = Event::findOrFail($request->id);
        $count = $action->handle($request, $event);

        if ($request->bulk) {
            flashMessage('success', __('Done'), __(':count slots have been created!', ['count' => $count]));
        } else {
            flashMessage('success', __('Done'), __('Slot created'));
        }

        return to_route('events.bookings.index', $event);
    }

    public function edit(Booking $booking): View|RedirectResponse
    {
        if ($booking->event->endEvent >= now()) {
            $airports = Airport::forDropdown();
            $flight = $booking->flights()->first();

            return view('booking.admin.edit', ['booking' => $booking, 'airports' => $airports, 'flight' => $flight]);
        }

        flashMessage('danger', __('Danger'), __('Booking can no longer be edited'));

        return back();
    }

    public function update(UpdateBooking $request, Booking $booking, UpdateBookingAction $action): RedirectResponse
    {
        $action->handle($request, $booking);

        flashMessage('success', __('Booking changed'), __('Booking has been changed!'));

        return to_route('events.bookings.index', $booking->event);
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        if ($booking->event->endEvent >= now()) {
            if ($booking->user_id) {
                event(new BookingDeleted($booking->event, $booking->user));
            }

            $booking->delete();
            flashMessage('success', __('Booking deleted!'), __('Booking has been deleted.'));

            return to_route('events.bookings.index', $booking->event);
        }

        flashMessage('danger', __('Danger'), __('Booking can no longer be deleted'));

        return back();
    }

    public function destroyAll(Event $event): RedirectResponse
    {
        if ($event->endEvent <= now()) {
            flashMessage('danger', __('Danger'), __('Booking can no longer be deleted'));

            return back();
        }

        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Delete all bookings');

        $event->bookings()->delete();
        flashMessage('success', __('Bookings deleted'), __('All bookings have been deleted'));

        return to_route('admin.events.index');
    }
}
