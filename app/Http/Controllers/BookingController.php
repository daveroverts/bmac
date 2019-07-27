<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Http\Requests\AdminAutoAssign;
use App\Http\Requests\AdminUpdateBooking;
use App\Http\Requests\ImportBookings;
use App\Http\Requests\StoreBooking;
use App\Http\Requests\UpdateBooking;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingChanged;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingDeleted;
use App\Policies\BookingPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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

        $this->authorizeResource(BookingPolicy::class, 'booking');
    }

    /**
     * Display a listing of the bookings.
     *
     * @param Request $request
     * @param Event|null $event
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, Event $event = null)
    {
        $this->removeOverdueReservations();

        //Check if specific event is requested, else fall back to current ongoing event
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
                if ($event->type->id !== EventType::ONEWAY) {
                    switch (strtolower($request->filter)) {
                        case 'departures':
                            $bookings = Booking::where('event_id', $event->id)
                                ->where('dep', $event->dep)
                                ->orderBy('ctot')
                                ->orderBy('callsign')
                                ->with(['airportDep', 'airportArr', 'event', 'user'])
                                ->get();
                            $filter = $request->filter;
                            break;
                        case 'arrivals':
                            $bookings = Booking::where('event_id', $event->id)
                                ->where('arr', $event->arr)
                                ->orderBy('eta')
                                ->orderBy('callsign')
                                ->with(['airportDep', 'airportArr', 'event', 'user'])
                                ->get();
                            $filter = $request->filter;
                            break;
                        default:
                            $bookings = Booking::where('event_id', $event->id)
                                ->orderBy('eta')
                                ->orderBy('ctot')
                                ->with(['airportDep', 'airportArr', 'event', 'user'])
                                ->get();
                    }
                } else {
                    $bookings = Booking::where('event_id', $event->id)
                        ->orderBy('eta')
                        ->orderBy('ctot')
                        ->with(['airportDep', 'airportArr', 'event', 'user'])
                        ->get();
                }
            } else {
                abort_unless(Auth::check() && Auth::user()->isAdmin, 404);
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
                $booking->status = BookingStatus::UNASSIGNED;
                $booking->user()->dissociate()->save();
            }
        }
    }

    /**
     * Show the form for creating new timeslots
     *
     * @param Event $event
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Event $event, Request $request)
    {
        $bulk = $request->bulk;
        $airports = Airport::orderBy('icao')->get();

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
        if ($request->bulk) {
            $event_start = Carbon::createFromFormat('Y-m-d H:i', $event->startEvent->toDateString() . ' ' . $request->start);
            $event_end = Carbon::createFromFormat('Y-m-d H:i', $event->endEvent->toDateString() . ' ' . $request->end);
            $separation = $request->separation * 60;
            $count = 0;
            for (; $event_start <= $event_end; $event_start->addSeconds($separation)) {
                $time = $event_start->copy();
                if ($time->second >= 30) {
                    $time->addMinute();
                }
                $time->second = 0;

                if (!Booking::where([
                    'event_id' => $request->id,
                    'ctot' => $time,
                    'dep' => $request->from,
                ])->first()) {
                    Booking::create([
                        'event_id' => $request->id,
                        'is_editable' => $request->is_editable,
                        'dep' => $request->dep,
                        'arr' => $request->arr,
                        'ctot' => $time,
                    ])->save();
                    $count++;
                }
            }
            flashMessage('success', 'Done', $count . ' Slots have been created!');
        } else {
            $booking = new Booking([
                'is_editable' => $request->is_editable,
                'callsign' => $request->callsign,
                'acType' => $request->aircraft,
                'dep' => $request->dep,
                'arr' => $request->arr,
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
        return redirect(route('bookings.event.index', $event));
    }

    /**
     * Display the specified booking.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        return view('booking.show', compact('booking'));
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
                return view('booking.edit', compact('booking'));
            } else {
                // Check if the booking has already been reserved
                if ($booking->status === BookingStatus::RESERVED) {
                    flashMessage('danger', 'Warning', 'Whoops! Somebody else reserved that slot just before you! Please choose another one. The slot will become available if it isn\'t confirmed within 10 minutes.');
                    return redirect(route('bookings.event.index', $booking->event));

                } // In case the booking has already been booked
                else {
                    flashMessage('danger', 'Warning', 'Whoops! Somebody else booked that slot just before you! Please choose another one.');
                    return redirect(route('bookings.event.index', $booking->event));
                }
            }
        } // If the booking hasn't been taken by anybody else, check if user doesn't already have a booking
        else {
            // If user already has another booking, but event only allows for 1
            if (!$booking->event->multiple_bookings_allowed && Auth::user()->bookings()->where('event_id', $booking->event_id)
                    ->where('status', BookingStatus::BOOKED)
                    ->first()) {
                flashMessage('danger!', 'Nope!', 'You already have a booking!');
                return redirect(route('bookings.event.index', $booking->event));
            }
            // If user already has another reservation open
            if (Auth::user()->bookings()->where('event_id', $booking->event_id)
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
                            ->by(Auth::user())
                            ->on($booking)
                            ->log('Flight reserved');
                        $booking->status = BookingStatus::RESERVED;
                        $booking->user()->associate(Auth::user())->save();
                        flashMessage('info', 'Slot reserved', 'Will remain reserved until ' . $booking->updated_at->addMinutes(10)->format('Hi') . 'z');
                        return view('booking.edit', compact('booking'));
                    } else {
                        flashMessage('danger', 'Nope!', 'Bookings have been closed at ' . $booking->event->endBooking->format('d-m-Y Hi') . 'z');
                        return redirect(route('bookings.event.index', $booking->event));
                    }
                } else {
                    flashMessage('danger', 'Nope!', 'Bookings aren\'t open yet. They will open at ' . $booking->event->startBooking->format('d-m-Y Hi') . 'z');
                    return redirect(route('bookings.event.index', $booking->event));
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
        if ($booking->is_editable) {
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
            activity()
                ->by(Auth::user())
                ->on($booking)
                ->log('Flight booked');
            $booking->user->notify(new BookingConfirmed($booking));
            flashMessage('success', 'Booking created!', 'Booking has been created! An E-mail with details has also been sent');
        } else {
            flashMessage('success', 'Booking edited!', 'Booking has been edited!');
        }
        $booking->save();
        return redirect(route('bookings.event.index', $booking->event));
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
     * Remove the specified booking from storage.
     *
     * @param Booking $booking
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Booking $booking)
    {
        if ($booking->event->endEvent >= now()) {
            $booking->delete();
            $message = 'Booking has been deleted.';
            if (!empty($booking->user)) {
                $message .= ' A E-mail has also been sent to the person that booked.';
                $booking->user->notify(new BookingDeleted($booking->event));
            }
            flashMessage('success', 'Booking deleted!', $message);
            return redirect(route('bookings.event.index', $booking->event));
        }
        flashMessage('danger', 'Nope!', 'You cannot delete a booking after the event ended');
        return back();
    }

    /**
     * Sets reservedBy and bookedBy to null.
     *
     * @param Booking $booking
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

                if ($booking->event->is_oceanic_event)
                    $booking->selcal = null;
            }
            $booking->status = BookingStatus::UNASSIGNED;
            activity()
                ->by(Auth::user())
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
        flashMessage('danger', 'Nope!', 'Bookings have been locked at ' . $booking->event->endBooking->format('d-m-Y Hi') . 'z');
        return redirect(route('bookings.event.index', $booking->event));
    }

    /**
     * Show the form for editing the specified booking.
     * @param Booking $booking
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function adminEdit(Booking $booking)
    {
        if ($booking->event->endEvent >= now()) {
            $airports = Airport::orderBy('icao')->get();
            return view('booking.admin.edit', compact('booking', 'airports'));
        }
        flashMessage('danger', 'Nope!', 'You cannot edit a booking after the event ended');
        return back();
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
            'is_editable' => $request->is_editable,
            'callsign' => $request->callsign,
            'dep' => $request->dep,
            'arr' => $request->arr,
            'route' => $request->route,
            'oceanicFL' => $request->oceanicFL,
            'oceanicTrack' => $request->oceanicTrack,
            'acType' => $request->aircraft,
        ]);
        if ($request->ctot)
            $booking->ctot = Carbon::createFromFormat('Y-m-d H:i', $booking->event->startEvent->toDateString() . ' ' . $request->ctot);

        if ($request->eta)
            $booking->eta = Carbon::createFromFormat('Y-m-d H:i', $booking->event->startEvent->toDateString() . ' ' . $request->eta);

        $changes = collect();
        if (!empty($booking->user)) {
            foreach ($booking->getDirty() as $key => $value) {
                $changes->push(
                    ['name' => $key, 'old' => $booking->getOriginal($key), 'new' => $value]
                );
            }
            if (!empty($request->message)) {
                $changes->push(
                    ['name' => 'message', 'new' => $request->message]
                );
            }
        }
        $booking->save();
        if (!empty($booking->user)) {
            $booking->user->notify(new BookingChanged($booking, $changes));
        }
        $message = 'Booking has been changed!';
        if (!empty($booking->user)) {
            $message .= ' A E-mail has also been sent to the person that booked.';
        }
        flashMessage('success', 'Booking changed', $message);
        return redirect(route('bookings.event.index', $booking->event));
    }

    /**
     * Exports all active bookings to a .csv file
     *
     * @param Event $event
     * @return string|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function export(Event $event)
    {
        activity()
            ->by(Auth::user())
            ->on($event)
            ->log('Export triggered');
        $bookings = Booking::where('event_id', $event->id)
            ->where('status', BookingStatus::BOOKED)
            ->get();
        return (new FastExcel($bookings))->withoutHeaders()->download('bookings.csv', function ($booking) {
            return [
                $booking->user->full_name,
                $booking->user_id,
                $booking->callsign,
                $booking->airportDep->icao,
                $booking->airportArr->icao,
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
        activity()
            ->by(Auth::user())
            ->on($event)
            ->log('Import triggered');
        $file = $request->file('file')->getRealPath();
        $bookings = collect();
        (new FastExcel)->importSheets($file, function ($line) use ($bookings, $event) {
            $dep = Airport::where('icao', $line['Origin'])->first();
            $arr = Airport::where('icao', $line['Destination'])->first();

            $flight = collect([
                'callsign' => $line['Call Sign'],
                'acType' => $line['Aircraft Type'],
                'dep' => $dep->id,
                'arr' => $arr->id,
            ]);
            if (isset($line['ETA'])) {
                $flight->put('eta', Carbon::createFromFormat('Y-m-d H:i', $event->startEvent->toDateString() . ' ' . $line['ETA']->format('H:i')));
            }
            if (isset($line['EOBT'])) {
                $flight->put('ctot', Carbon::createFromFormat('Y-m-d H:i', $event->startEvent->toDateString() . ' ' . $line['EOBT']->format('H:i')));
            }
            $bookings->push($flight);
        });
        $event->bookings()->createMany($bookings->toArray());
        Storage::delete($file);
        flashMessage('success', 'Flights imported', 'Flights have been imported');
        return redirect(route('bookings.event.index', $event));
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
        activity()
            ->by(Auth::user())
            ->on($event)
            ->withProperties(
                [
                    'Track 1' => $request->oceanicTrack1,
                    'Route 1' => $request->route1,
                    'Track 2' => $request->oceanicTrack2,
                    'Route 2' => $request->route2,
                    'count' => $count,
                ]
            )
            ->log('Flights auto-assigned');
        return redirect(route('events.index'));
    }
}
