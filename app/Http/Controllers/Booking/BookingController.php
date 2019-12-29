<?php

namespace App\Http\Controllers\Booking;

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Http\Requests\Booking\UpdateBooking;
use App\Models\Booking;
use App\Models\Event;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingConfirmed;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
    /**
     * Display a listing of the bookings.
     *
     * @param  Request  $request
     * @param  Event|null  $event
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Event $event = null)
    {
        $this->removeOverdueReservations();

        // Check if specific event is requested, else fall back to current ongoing event
        if (!$event) {
            $event = nextEvent();
            if (!empty($event)) {
                return redirect(route('bookings.event.index', $event));
            }
        }

        $bookings = collect();
        $filter = null;

        if ($event) {
            if ($event->is_online) {
                if ($event->hasOrderButtons()) {
                    switch (strtolower($request->filter)) {
                        case 'departures':
                            $bookings = Booking::where('event_id', $event->id)
                                ->orderBy('callsign')
                                ->with([
                                    'event',
                                    'user',
                                    'flights' => function ($query) use ($event) {
                                        $query->where('dep', $event->dep);
                                        $query->orderBy('ctot');
                                    }
                                ])
                                ->get();
                            $filter = $request->filter;
                            break;
                        case 'arrivals':
                            $bookings = Booking::where('event_id', $event->id)
                                ->orderBy('callsign')
                                ->with([
                                    'event',
                                    'user',
                                    'flights' => function ($query) use ($event) {
                                    $query->where('dep', $event->arr);
                                    $query->orderBy('eta');
                                    }
                                ])
                                ->get();
                            $filter = $request->filter;
                            break;
                        default:
                            $bookings = Booking::where('event_id', $event->id)
                                ->with([
                                    'flights' => function ($query) {
                                    $query->orderBy('eta');
                                    $query->orderBy('ctot');
                                    },
                                ])
                                ->get();
                    }
                } else {
                    $bookings = Booking::where('event_id', $event->id)
                        ->with([
                            'event',
                            'user',
                            'flights' => function ($query) {
                                $query->orderBy('order_by');
                                $query->orderBy('eta');
                                $query->orderBy('ctot');
                            },
                        ])
                        ->get();
                }
            } else {
                abort_unless(auth()->check() && auth()->user()->isAdmin, 404);
            }
        }
        if ($event->event_type_id == EventType::MULTIFLIGHTS) {
            return view('booking.overview_multiflights', compact('event', 'bookings', 'filter'));
        }
        return view('booking.overview', compact('event', 'bookings', 'filter'));
    }

    public function removeOverdueReservations()
    {
        // Get all reservations that have been reserved
        Booking::whereStatus(BookingStatus::RESERVED)->each(function ($booking) {
            // If a reservation has been reserved for more then 10 minutes, remove user_id, and make booking available
            if (now() > Carbon::createFromFormat('Y-m-d H:i:s', $booking->updated_at)->addMinutes(10)) {
                $booking->status = BookingStatus::UNASSIGNED;
                $booking->user()->dissociate()->save();
            }
        });
    }

    /**
     * Display the specified booking.
     *
     * @param  Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        if ($booking->event->event_type_id == EventType::MULTIFLIGHTS) {
            return view('booking.show_multiflights', compact('booking'));
        }
        return view('booking.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        // Check if the booking has already been booked or reserved
        if ($booking->status !== BookingStatus::UNASSIGNED) {
            // Check if current user has booked/reserved
            if ($booking->user_id == auth()->id()) {
                if ($booking->status == BookingStatus::BOOKED && !$booking->is_editable) {
                    flashMessage('info', 'Nope!', 'You cannot edit the booking!');
                    return redirect(route('bookings.event.index', $booking->event));
                }
                if ($booking->event->event_type_id == EventType::MULTIFLIGHTS) {
                    return view('booking.edit_multiflights', compact('booking'));
                }
                return view('booking.edit', compact('booking'));
            } else {
                // Check if the booking has already been reserved
                if ($booking->status === BookingStatus::RESERVED) {
                    flashMessage('danger', 'Warning',
                        'Whoops! Somebody else reserved that slot just before you! Please choose another one. The slot will become available if it isn\'t confirmed within 10 minutes.');
                    return redirect(route('bookings.event.index', $booking->event));

                } // In case the booking has already been booked
                else {
                    flashMessage('danger', 'Warning',
                        'Whoops! Somebody else booked that slot just before you! Please choose another one.');
                    return redirect(route('bookings.event.index', $booking->event));
                }
            }
        } // If the booking hasn't been taken by anybody else, check if user doesn't already have a booking
        else {
            // If user already has another booking, but event only allows for 1
            if (!$booking->event->multiple_bookings_allowed && auth()->user()->bookings()->where('event_id',
                    $booking->event_id)
                    ->where('status', BookingStatus::BOOKED)
                    ->first()) {
                flashMessage('danger!', 'Nope!', 'You already have a booking!');
                return redirect(route('bookings.event.index', $booking->event));
            }
            // If user already has another reservation open
            if (auth()->user()->bookings()->where('event_id', $booking->event_id)
                ->where('status', BookingStatus::RESERVED)
                ->first()) {
                flashMessage('danger', 'Nope!', 'You already have a reservation!');
                return redirect(route('bookings.event.index', $booking->event));
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
                        flashMessage('info', 'Slot reserved',
                            'Will remain reserved until '.$booking->updated_at->addMinutes(10)->format('Hi').'z');
                        if ($booking->event->event_type_id == EventType::MULTIFLIGHTS) {
                            return view('booking.edit_multiflights', compact('booking'));
                        }
                        return view('booking.edit', compact('booking'));
                    } else {
                        flashMessage('danger', 'Nope!',
                            'Bookings have been closed at '.$booking->event->endBooking->format('d-m-Y Hi').'z');
                        return redirect(route('bookings.event.index', $booking->event));
                    }
                } else {
                    flashMessage('danger', 'Nope!',
                        'Bookings aren\'t open yet. They will open at '.$booking->event->startBooking->format('d-m-Y Hi').'z');
                    return redirect(route('bookings.event.index', $booking->event));
                }
            }
        }
    }

    /**
     * Update the specified booking in storage.
     *
     * @param  UpdateBooking  $request
     * @param  Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBooking $request, Booking $booking)
    {
        // This check should actually be in the policy, but is now here as a quick fix
        if ($booking->user_id === $request->user()->id) {
            if ($booking->is_editable) {
                $booking->fill([
                    'callsign' => $request->callsign,
                    'acType' => $request->aircraft
                ]);
            }

            if ($booking->event->is_oceanic_event) {
                $booking->selcal = $this->validateSELCAL(strtoupper($request->selcal1.'-'.$request->selcal2),
                    $booking->event_id);
            }

            $booking->status = BookingStatus::BOOKED;
            if ($booking->getOriginal('status') === BookingStatus::RESERVED) {
                activity()
                    ->by(auth()->user())
                    ->on($booking)
                    ->log('Flight booked');
                $booking->user->notify(new BookingConfirmed($booking));
                flashMessage('success', 'Booking created!',
                    'Booking has been created! An E-mail with details has also been sent');
            } else {
                flashMessage('success', 'Booking edited!', 'Booking has been edited!');
            }
            $booking->save();
            return redirect(route('bookings.event.index', $booking->event));
        } else {
            abort(403);
        }
    }

    public function validateSELCAL($selcal, $eventId)
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
        if (substr_count($selcal, $char1) > 1 || substr_count($selcal, $char2) > 1 || substr_count($selcal,
                $char3) > 1 || substr_count($selcal, $char4) > 1) {
            return null;
        }

        // Check if characters per pair are in alphabetical order
        if ($char1 > $char2 || $char3 > $char4) {
            return null;
        }

        // Check for duplicates within the same event
        if (Booking::where('event_id', $eventId)
            ->where('selcal', '=', $selcal)
            ->get()->first()) {
            return null;
        }
        return $selcal;
    }

    /**
     * Sets reservedBy and bookedBy to null.
     *
     * @param  Booking  $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function cancel(Booking $booking)
    {
        $this->authorize('cancel', $booking);
        if ($booking->event->endBooking > now()) {
            if ($booking->is_editable) {
                $booking->fill([
                    'callsign' => null,
                    'acType' => null,
                ]);

                if ($booking->event->is_oceanic_event) {
                    $booking->selcal = null;
                }
            }
            $booking->status = BookingStatus::UNASSIGNED;
            activity()
                ->by(auth()->user())
                ->on($booking)
                ->log('Flight available');
            if ($booking->getOriginal('status') === BookingStatus::BOOKED) {
                $title = 'Booking removed!';
                $message = 'Booking has been removed! A E-mail has also been sent';
                $booking->user->notify(new BookingCancelled($booking->event));

            } else {
                $title = 'Slot free';
                $message = 'Slot is now free to use again';
            }
            flashMessage('info', $title, $message);
            $booking->user()->dissociate()->save();
            return redirect(route('bookings.event.index', $booking->event));
        }
        flashMessage('danger', 'Nope!',
            'Bookings have been locked at '.$booking->event->endBooking->format('d-m-Y Hi').'z');
        return redirect(route('bookings.event.index', $booking->event));
    }
}
