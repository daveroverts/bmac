<?php

namespace App\Http\Controllers;

use App\{Enums\BookingStatus,
    Http\Requests\AdminAutoAssign,
    Http\Requests\AdminUpdateBooking,
    Http\Requests\ImportBookings,
    Http\Requests\StoreBooking,
    Http\Requests\UpdateBooking,
    Mail\BookingCancelled,
    Mail\BookingChanged,
    Mail\BookingConfirmed,
    Mail\BookingDeleted,
    Models\Airport,
    Models\Booking,
    Models\Event};
use Carbon\Carbon;
use Illuminate\{Http\Request, Support\Facades\Auth, Support\Facades\Mail, Support\Facades\Storage};
use Rap2hpoutre\FastExcel\FastExcel;

class BookingController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.isLoggedIn')->except('index');

        $this->middleware('auth.isAdmin')->only(['create', 'store', 'destroy', 'adminEdit', 'adminUpdate', 'export', 'importForm', 'import', 'adminAutoAssignForm', 'adminAutoAssign']);
    }

    /**
     * Display a listing of the bookings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Event $event = null)
    {
        $this->removeOverdueReservations();

        //Check if specific event is requested, else fall back to current ongoing event
        if (!$event)
            $event = Event::where('endEvent', '>', now())->orderBy('startEvent', 'desc')->first();

        $bookings = collect();

        $filter = null;

        if ($event) {
            switch (strtolower($request->filter)) {
                case 'departures':
                    $bookings = Booking::where('event_id', $event->id)
                        ->where('dep', $event->dep)
                        ->orderBy('ctot')
                        ->get();
                    $filter = $request->filter;
                    break;
                case 'arrivals':
                    $bookings = Booking::where('event_id', $event->id)
                        ->where('arr', $event->arr)
                        ->orderBy('ctot')
                        ->get();
                    $filter = $request->filter;
                    break;
                default:
                    $bookings = Booking::where('event_id', $event->id)->orderBy('ctot')->get();
            }
        }

        return view('booking.overview', compact('event', 'bookings', 'filter'));
    }

    public function removeOverdueReservations()
    {
        // Get all reservations that have been reserved
        foreach (Booking::where('status', BookingStatus::RESERVED)->get() as $booking) {
            // If a reservation has been reserved for more then 10 minutes, remove user_id, and make booking available
            if (now() > Carbon::createFromFormat('Y-m-d H:i:s', $booking->updated_at)->addMinutes(10)) {
                $booking->fill([
                    'status' => BookingStatus::UNASSIGNED,
                ]);
                $booking->user()->dissociate()->save();
            }
        }
    }

    /**
     * Show the form for creating new timeslots
     *
     * @param Event $event
     * @return \Illuminate\Http\Response
     */
    public function create(Event $event, Request $request)
    {
        $bulk = $request->bulk;
        $airports = Airport::all();

        return view('booking.create', compact('event', 'airports', 'bulk'));
    }

    /**
     * Store new timeslots in storage.
     *
     * @param StoreBooking $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBooking $request)
    {
        $event = Event::whereKey($request->id)->first();
        $from = Airport::findOrFail($request->from);
        $to = Airport::findOrFail($request->to);
        if ($request->bulk) {
            $event_start = Carbon::createFromFormat('Y-m-d H:i', $event->startEvent->toDateString() . ' ' . $request->start);
            $event_end = Carbon::createFromFormat('Y-m-d H:i', $event->endEvent->toDateString() . ' ' . $request->end);
            $separation = $request->separation;
            $count = 0;
            for ($event_start; $event_start <= $event_end; $event_start->addMinutes($separation)) {
                if (!Booking::where([
                    'event_id' => $request->id,
                    'ctot' => $event_start,
                ])->first()) {
                    Booking::create([
                        'event_id' => $request->id,
                        'dep' => $from->icao,
                        'arr' => $to->icao,
                        'ctot' => $event_start,
                    ])->save();
                    $count++;
                }
            }
            flashMessage('success', 'Done', $count . ' Slots have been created!');
        } else {
            $booking = new Booking([
                'callsign' => $request->callsign,
                'acType' => $request->aircraft,
                'dep' => $from->icao,
                'arr' => $to->icao,
                'route' => $request->route,
                'oceanicFL' => $request->oceanicFL,
            ]);
            if ($request->ctot) {
                $booking->ctot = Carbon::createFromFormat('Y-m-d H:i', $event->startEvent->toDateString() . ' ' . $request->ctot);
            }

            if ($request->eta) {
                $booking->eta = Carbon::createFromFormat('Y-m-d H:i', $event->startEvent->toDateString() . ' ' . $request->eta);
            }
            $booking->event()->associate($request->id)->save();
            flashMessage('success', 'Done', 'Slot created');
        }
        return redirect(route('booking.index'));
    }

    /**
     * Display the specified booking.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        if (Auth::check() && Auth::id() == $booking->user_id || Auth::user()->isAdmin) {
            return view('booking.show', compact('booking'));
        } else {
            flashMessage('danger', 'Already booked', 'Whoops that booking belongs to somebody else!');
            return redirect(route('booking.index'));
        }
    }

    /**
     * Show the form for editing the specified booking.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\Response
     */
    public function edit(Booking $booking)
    {
        // Check if the booking has already been booked or reserved
        if ($booking->status !== BookingStatus::UNASSIGNED) {
            // Check if current user has booked/reserved
            if ($booking->user_id == Auth::id()) {
                flashMessage('info', 'Slot reserved', 'Will remain reserved until ' . $booking->updated_at->addMinutes(10)->format('Hi') . 'z');
                return view('booking.edit', compact('booking', 'user'));
            } else {
                // Check if the booking has already been reserved
                if ($booking->status === BookingStatus::RESERVED) {
                    flashMessage('danger', 'Warning', 'Whoops! Somebody else reserved that slot just before you! Please choose another one. The slot will become available if it isn\'t confirmed within 10 minutes.');
                    return redirect(route('booking.index'));

                } // In case the booking has already been booked
                else {
                    flashMessage('danger', 'Warning', 'Whoops! Somebody else booked that slot just before you! Please choose another one.');
                    return redirect(route('booking.index'));
                }
            }
        } // If the booking hasn't been taken by anybody else, check if user doesn't already have a booking
        else {
            // If user already has another reservation open
            if (Auth::user()->booking()->where('event_id', $booking->event_id)
                ->where('status', BookingStatus::RESERVED)
                ->first()) {
                flashMessage('danger!', 'Nope!', 'You already have a reservation!');
                return redirect(route('booking.index'));
            } // Reserve booking, and redirect to booking.edit
            else {
                // Check if you are allowed to reserve the slot
                if ($booking->event->startBooking < now()) {
                    if ($booking->event->endBooking > now()) {
                        $booking->status = BookingStatus::RESERVED;
                        $booking->user()->associate(Auth::user())->save();
                        flashMessage('info', 'Slot reserved', 'Will remain reserved until ' . $booking->updated_at->addMinutes(10)->format('Hi') . 'z');
                        return view('booking.edit', compact('booking', 'user'));
                    } else {
                        flashMessage('danger', 'Nope!', 'Bookings have been closed at ' . $booking->event->endBooking->format('d-m-Y Hi') . 'z');
                        return redirect(route('booking.index'));
                    }
                } else {
                    flashMessage('danger', 'Nope!', 'Bookings aren\'t open yet. They will open at ' . $booking->event->startBooking->format('d-m-Y Hi') . 'z');
                    return redirect(route('booking.index'));
                }
            }
        }
    }

    /**
     * Update the specified booking in storage.
     *
     * @param UpdateBooking $request
     * @param Booking $booking
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBooking $request, Booking $booking)
    {
        // Check if the reservation / booking actually belongs to the correct person
        if (Auth::id() == $booking->user_id) {
            if (!$booking->event->import_only) {
                $booking->fill([
                    'callsign' => $request->callsign,
                    'acType' => $request->aircraft
                ]);
            }

            if ($booking->event->is_oceanic_event) {
                $booking->selcal = $this->validateSELCAL(strtoupper($request->selcal1 . '-' . $request->selcal2), $booking->event_id);
            }

            $booking->status = BookingStatus::BOOKED;
            if ($booking->getOriginal('status') === BookingStatus::RESERVED) {
                Mail::to(Auth::user())->send(new BookingConfirmed($booking));
                flashMessage('success', 'Booking created!', 'Booking has been created! An E-mail with details has also been sent');
            } else {
                flashMessage('success', 'Booking edited!', 'Booking has been edited!');
            }
            $booking->save();
            return redirect(route('booking.index'));
        } else {
            if ($booking->user_id != null) {
                // We got a bad-ass over here, log that person out
                Auth::logout();
                return redirect('https://youtu.be/dQw4w9WgXcQ');
            } else {
                flashMessage('warning', 'Nope!', 'That reservation does not belong to you!');
                return redirect(route('booking.index'));
            }
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
        if (substr_count($selcal, $char1) > 1 || substr_count($selcal, $char2) > 1 || substr_count($selcal, $char3) > 1 || substr_count($selcal, $char4) > 1) {
            return null;
        }

        // Check if characters per pair are in alphabetical order
        if ($char1 > $char2 || $char4 > $char3) {
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
     * Remove the specified booking from storage.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\Response
     */
    public function destroy(Booking $booking)
    {
        $booking->delete();
        $message = 'Booking has been deleted.';
        if (!empty($booking->user)) {
            $message .= ' A E-mail has also been sent to the person that booked.';
            Mail::to(Auth::user())->send(new BookingDeleted($booking->event, $booking->user));
        }
        flashMessage('success', 'Booking deleted!', $message);
        return redirect(route('booking.index'));
    }

    /**
     * Sets reservedBy and bookedBy to null.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function cancel(Booking $booking)
    {
        if (Auth::id() == $booking->user_id) {
            if ($booking->event->endBooking > now()) {
//                $booking->fill([
//                    'status' => BookingStatus::UNASSIGNED,
//                    'callsign' => null,
//                    'acType' => null,
//                    'selcal' => null,
//                ]);
                $booking->status = BookingStatus::UNASSIGNED;
                if ($booking->getOriginal('status') === BookingStatus::BOOKED) {
                    $title = 'Booking removed!';
                    $message = 'Booking has been removed! A E-mail has also been sent';
                    Mail::to(Auth::user())->send(new BookingCancelled($booking->event, Auth::user()));
                } else {
                    $title = 'Slot free';
                    $message = 'Slot is now free to use again';
                }
                flashMessage('info', $title, $message);
                $booking->user()->dissociate()->save();
                return redirect(route('booking.index'));
            }
            flashMessage('danger', 'Nope!', 'Bookings have been locked at ' . $booking->event->endBooking->format('d-m-Y Hi') . 'z');

        } else {
            // We got a bad-ass over here, log that person out
            Auth::logout();
            return redirect('https://youtu.be/dQw4w9WgXcQ');
        }
    }

    /**
     * Show the form for editing the specified booking.
     * @param Booking $booking
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function adminEdit(Booking $booking)
    {
        $airports = Airport::all();

        return view('booking.admin.edit', compact('booking', 'airports'));
    }

    /**
     * Updates booking.
     *
     * @param AdminUpdateBooking $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function adminUpdate(AdminUpdateBooking $request, Booking $booking)
    {
        $booking->fill([
            'callsign' => $request->callsign,
            'ctot' => Carbon::createFromFormat('Y-m-d H:i', $booking->event->startEvent->toDateString() . ' ' . $request->ctot),
            'dep' => $request->ADEP,
            'arr' => $request->ADES,
            'route' => $request->route,
            'oceanicFL' => $request->oceanicFL,
            'oceanicTrack' => $request->oceanicTrack,
            'acType' => $request->aircraft,
        ]);
        $changes = collect();
        if (!empty($booking->user)) {
            foreach ($booking->getDirty() as $key => $value) {
                $changes->push(
                    ['name' => $key, 'old' => $booking->getOriginal($key), 'new' => $value]
                );
            }
        }
        if (!empty($request->message)) {
            $changes->push(
                ['name' => 'message', 'new' => $request->message]
            );
        }
        $booking->save();
        if (!empty($booking->user)) {
            Mail::to($booking->user->email)->send(new BookingChanged($booking, $changes));
        }
        $message = 'Booking has been changed!';
        if (!empty($booking->user)) {
            $message .= ' A E-mail has also been sent to the person that booked.';
        }
        flashMessage('success', 'Booking changed', $message);
        return redirect(route('booking.index'));
    }

    /**
     * Exports all active bookings to a .csv file
     *
     * @param Event $$event
     */
    public function export(Event $event)
    {
        $bookings = Booking::where('event_id', $event->id)
            ->where('status', BookingStatus::BOOKED)
            ->get();
        return (new FastExcel($bookings))->withoutHeaders()->download('bookings.csv', function ($booking) {
            return [
                $booking->user->full_name,
                $booking->user_id,
                $booking->callsign,
                $booking->dep,
                $booking->arr,
                $booking->getOriginal('oceanicFL'),
                Carbon::parse($booking->getOriginal('ctot'))->format('H:i:s'),
                $booking->route,
            ];
        });
    }

    public function importForm(Event $event)
    {
        return view('event.admin.import', compact('event'));
    }

    public function import(ImportBookings $request, Event $event)
    {
        $file = $request->file('file')->getRealPath();
        $bookings = collect();
        $collection = (new FastExcel)->importSheets($file, function ($line) use ($bookings) {
            $bookings->push([
                'callsign' => $line['CS'],
                'acType' => $line['ATYP'],
                'dep' => $line['ADEP'],
                'arr' => $line['ADES'],
                ]);
        });
        $event->bookings()->createMany($bookings->toArray());
        Storage::delete($file);
        flashMessage('success', 'Flights imported', 'Flights have been imported');
        return redirect(route('booking.index', $event));
    }

    /**
     * Show the form for editing the specified booking.
     *
     * @param Event $event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function adminAutoAssignForm(Event $event)
    {
        return view('event.admin.autoAssign', compact('event'));
    }

    /**
     * @param AdminAutoAssign $request
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function adminAutoAssign(AdminAutoAssign $request, Event $event)
    {
        $bookings = Booking::where('event_id', $event->id)
            ->where('status', BookingStatus::BOOKED)
            ->orderBy('ctot')
            ->get();
        $count = 0;
        $flOdd = $request->maxFL;
        $flEven = $request->minFL;
        foreach ($bookings as $booking) {
            $count++;
            if ($count % 2 == 0) {
                $booking->fill([
                    'oceanicTrack' => $request->oceanicTrack2,
                    'route' => $request->route2,
                    'oceanicFL' => $flEven,
                ]);
                $flEven = $flEven + 10;
                if ($flEven > $request->maxFL) {
                    $flEven = $request->minFL;
                }
            } else {
                $booking->fill([
                    'oceanicTrack' => $request->oceanicTrack1,
                    'route' => $request->route1,
                    'oceanicFL' => $flOdd,
                ]);
                $flOdd = $flOdd - 10;
                if ($flOdd < $request->minFL) {
                    $flOdd = $request->maxFL;
                }
            }
            $booking->save();

        }
        flashMessage('success', 'Bookings changed', $count . ' Bookings have been Auto-Assigned a FL, and route');
        return redirect(route('event.index'));
    }
}
