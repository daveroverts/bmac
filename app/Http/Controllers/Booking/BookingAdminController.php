<?php

namespace App\Http\Controllers\Booking;

use App\Enums\BookingStatus;
use App\Http\Controllers\AdminController;
use App\Http\Requests\Booking\Admin\AutoAssign;
use App\Http\Requests\Booking\Admin\UpdateBooking;
use App\Http\Requests\Booking\Admin\ImportBookings;
use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Notifications\BookingChanged;
use App\Notifications\BookingDeleted;
use App\Policies\BookingPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Rap2hpoutre\FastExcel\FastExcel;

class BookingAdminController extends AdminController
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->authorizeResource(BookingPolicy::class, 'booking');
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

        return view('booking.admin.create', compact('event', 'airports', 'bulk'));
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
     * @param Booking $booking
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Booking $booking)
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
     * @param UpdateBooking $request
     * @param Booking $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(UpdateBooking $request, Booking $booking)
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
     * @param AutoAssign $request
     * @param Event $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function adminAutoAssign(AutoAssign $request, Event $event)
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
        return redirect(route('admin.events.index'));
    }
}
