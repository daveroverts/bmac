<?php

namespace App\Http\Controllers\Booking;

use App\Models\Event;
use App\Models\Booking;
use App\Enums\EventType;
use Illuminate\Support\Str;
use App\Enums\BookingStatus;
use Illuminate\Http\Request;
use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\UpdateBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index(Request $request, Event $event): View|RedirectResponse
    {
        return view('booking.overview', compact('event'));
    }

    public function show(Booking $booking): View
    {
        if ($booking->event->event_type_id == EventType::MULTIFLIGHTS) {
            return view('booking.show_multiflights', compact('booking'));
        }
        $flight = $booking->flights->first();
        $confirmed = $booking->confirmed_at;
        $startConfirm = $booking->event->startConfirm;
        $endConfirm = $booking->event->endConfirm;
        return view('booking.show', compact('booking', 'flight','confirmed','startConfirm','endConfirm'));
    }

    public function confirm(Booking $booking): RedirectResponse
    {
        $booking->confirmed_at = Carbon::now();
        $booking->save();

        flashMessage('success', __('Done'), __('Event has been updated!'));
        return to_route('bookings.show',$booking);
    }

    // TODO: Split this in multiple functions/routes. This is just one big mess
    public function edit(Booking $booking): View|RedirectResponse
    {
        // Check if the booking has already been booked or reserved
        if ($booking->status !== BookingStatus::UNASSIGNED) {
            // Check if current user has booked/reserved
            if ($booking->user_id == auth()->id()) {
                if ($booking->status == BookingStatus::BOOKED && !$booking->is_editable) {
                    flashMessage('info', __('Danger'), __('You cannot edit the booking!'));
                    return to_route('bookings.event.index', $booking->event);
                }
                if ($booking->event->event_type_id == EventType::MULTIFLIGHTS) {
                    return view('booking.edit_multiflights', compact('booking'));
                }
                $flight = $booking->flights->first();
                return view('booking.edit', compact('booking', 'flight'));
            } else {
                // Check if the booking has already been reserved
                if ($booking->status === BookingStatus::RESERVED) {
                    flashMessage(
                        'danger',
                        __('Warning'),
                        __('Whoops! Somebody else reserved that slot just before you! Please choose another one. The slot will become available if it isn\'t confirmed within 10 minutes.')
                    );
                    return to_route('bookings.event.index', $booking->event);
                } // In case the booking has already been booked
                else {
                    flashMessage(
                        'danger',
                        __('Warning'),
                        __('Whoops! Somebody else booked that slot just before you! Please choose another one.')
                    );
                    return to_route('bookings.event.index', $booking->event);
                }
            }
        } // If the booking hasn't been taken by anybody else, check if user doesn't already have a booking
        else {
            // If user already has another booking, but event only allows for 1
            if (
                !$booking->event->multiple_bookings_allowed && auth()->user()->bookings->where(
                    'event_id',
                    $booking->event_id
                )
                ->where('status', BookingStatus::BOOKED)
                ->first()
            ) {
                flashMessage('danger!', __('Warning'), __('You already have a booking!'));
                return to_route('bookings.event.index', $booking->event);
            }
            // If user already has another reservation open
            if (auth()->user()->bookings->where('event_id', $booking->event_id)
                ->where('status', BookingStatus::RESERVED)
                ->first()
            ) {
                flashMessage('danger', __('Warning'), __('You already have a reservation! Please cancel or book that flight first.'));
                return to_route('bookings.event.index', $booking->event);
            } // Reserve booking, and redirect to booking.edit
            else {
                // Check if you are allowed to reserve the slot
                if ($booking->event->startBooking <= now()) {
                    if ($booking->event->endBooking >= now()) {
                        activity()
                            ->by(auth()->user())
                            ->on($booking)
                            ->log('Flight reserved');
                        $booking->status = BookingStatus::RESERVED;
                        $booking->user()->associate(auth()->user())->save();
                        flashMessage(
                            'info',
                            __('Slot reserved'),
                            __('Slot remains reserved until :time', ['time' => $booking->updated_at->addMinutes(10)->format('Hi') . 'z'])
                        );
                        if ($booking->event->event_type_id == EventType::MULTIFLIGHTS) {
                            return view('booking.edit_multiflights', compact('booking'));
                        }
                        $flight = $booking->flights->first();
                        return view('booking.edit', compact('booking', 'flight'));
                    } else {
                        flashMessage(
                            'danger',
                            __('Danger'),
                            __('Bookings have been closed at :time', ['time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z'])
                        );
                        return to_route('bookings.event.index', $booking->event);
                    }
                } else {
                    flashMessage(
                        'danger',
                        __('Danger'),
                        __('Bookings aren\'t open yet. They will open at :time', ['time' => $booking->event->startBooking->format('d-m-Y Hi') . 'z'])
                    );
                    return to_route('bookings.event.index', $booking->event);
                }
            }
        }
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

            if ($booking->event->is_oceanic_event) {
                $booking->selcal = $this->validateSELCAL(
                    strtoupper($request->selcal1 . '-' . $request->selcal2),
                    $booking->event_id
                );
            }

            $booking->status = BookingStatus::BOOKED;
            if ($booking->getOriginal('status') === BookingStatus::RESERVED) {
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
        } else {
            abort(403);
        }
    }

    public function validateSELCAL($selcal, $eventId): ?string
    {
        // Separate characters
        $char1 = substr($selcal, 0, 1);
        $char2 = substr($selcal, 1, 1);
        $char3 = substr($selcal, 3, 1);
        $char4 = substr($selcal, 4, 1);

        // Check if SELCAL has valid format
        if (!preg_match("/[ABCDEFGHJKLMPQRS]{2}[-][ABCDEFGHJKLMPQRS]{2}/", $selcal)) {
            return null;
        }

        // Check if each character is unique
        if (substr_count($selcal, $char1) > 1 || substr_count($selcal, $char2) > 1 || substr_count(
            $selcal,
            $char3
        ) > 1 || substr_count($selcal, $char4) > 1) {
            return null;
        }

        // Check if characters per pair are in alphabetical order
        if ($char1 > $char2 || $char3 > $char4) {
            return null;
        }

        // Check for duplicates within the same event
        if (Booking::where('event_id', $eventId)
            ->where('selcal', '=', $selcal)
            ->first()
        ) {
            return null;
        }
        return $selcal;
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        $this->authorize('cancel', $booking);
        if ($booking->event->endBooking > now()) {
            if ($booking->is_editable) {
                $booking->fill([
                    'callsign' => null,
                    'acType' => null,
                    'selcal' => null,
                    'confirmed_at' => null,
                ]);
            }

            if ($booking->status === BookingStatus::BOOKED) {
                event(new BookingCancelled($booking, auth()->user()));
                $title = __('Booking cancelled!');
                $message = __('Booking has been cancelled!');
            } else {
                $title = __('Slot free');
                $message = __('Slot is now free to use again');
            }

            $booking->status = BookingStatus::UNASSIGNED;
            flashMessage('info', $title, $message);
            $booking->user()->dissociate()->save();
            return to_route('bookings.event.index', $booking->event);
        }
        flashMessage(
            'danger',
            __('Danger'),
            __('Bookings have been locked at :time', ['time' => $booking->event->endBooking->format('d-m-Y Hi') . 'z'])
        );
        return to_route('bookings.event.index', $booking->event);
    }
}
