<?php

namespace App\Http\Controllers\Booking;

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Events\BookingChanged;
use App\Events\BookingDeleted;
use App\Http\Controllers\AdminController;
use App\Http\Requests\Booking\Admin\AutoAssign;
use App\Http\Requests\Booking\Admin\ImportBookings;
use App\Http\Requests\Booking\Admin\RouteAssign;
use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Http\Requests\Booking\Admin\UpdateBooking;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Policies\BookingPolicy;
use Carbon\Carbon;
use Illuminate\Http\Request;
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
     * @param  Event  $event
     * @param  Request  $request
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
     * @param  StoreBooking  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBooking $request)
    {
        $event = Event::whereKey($request->id)->first();
        if ($request->bulk) {
            $event_start = Carbon::createFromFormat('Y-m-d H:i',
                $event->startEvent->toDateString().' '.$request->start);
            $event_end = Carbon::createFromFormat('Y-m-d H:i', $event->endEvent->toDateString().' '.$request->end);
            $separation = $request->separation * 60;
            $count = 0;
            for (; $event_start <= $event_end; $event_start->addSeconds($separation)) {
                $time = $event_start->copy();
                if ($time->second >= 30) {
                    $time->addMinute();
                }
                $time->second = 0;

                if (!Flight::whereHas('booking', function ($query) use ($request) {
                    $query->where('event_id', $request->id);
                })->where([
                    'ctot' => $time,
                    'dep' => $request->dep,
                ])->first()) {
                    Booking::create([
                        'event_id' => $request->id,
                        'is_editable' => $request->is_editable,
                    ])->flights()->create([
                        'dep' => $request->dep,
                        'arr' => $request->arr,
                        'ctot' => $time,
                        'notes' => $request->notes ?? null,
                    ]);

                    $count++;
                }
            }
            flashMessage('success', 'Done', $count.' Slots have been created!');
        } else {
            $booking = new Booking([
                'is_editable' => $request->is_editable,
                'callsign' => $request->callsign,
                'acType' => $request->aircraft,
            ]);

            $booking->event()->associate($request->id)->save();
            $flightAttributes = [
                'dep' => $request->dep,
                'arr' => $request->arr,
                'route' => $request->route,
                'oceanicFL' => $request->oceanicFL,
                'notes' => $request->notes ?? null,
            ];

            if ($request->ctot) {
                $flightAttributes['ctot'] = Carbon::createFromFormat('Y-m-d H:i',
                    $event->startEvent->toDateString().' '.$request->ctot);
            }

            if ($request->eta) {
                $flightAttributes['eta'] = Carbon::createFromFormat('Y-m-d H:i',
                    $event->startEvent->toDateString().' '.$request->eta);
            }

            $booking->flights()->create($flightAttributes);
            flashMessage('success', 'Done', 'Slot created');
        }
        return redirect(route('bookings.event.index', $event));
    }

    /**
     * Display the specified booking.
     *
     * @param  Booking  $booking
     * @return \Illuminate\Http\Response
     */
    public function show(Booking $booking)
    {
        return view('booking.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified booking.
     * @param  Booking  $booking
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Booking $booking)
    {
        if ($booking->event->endEvent >= now()) {
            $airports = Airport::orderBy('icao')->get();
            $flight = $booking->flights()->first();
            return view('booking.admin.edit', compact('booking', 'airports', 'flight'));
        }
        flashMessage('danger', 'Nope!', 'You cannot edit a booking after the event ended');
        return back();
    }

    /**
     * Updates booking.
     *
     * @param  UpdateBooking  $request
     * @param  Booking  $booking
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(UpdateBooking $request, Booking $booking)
    {
        $shouldSendEmail = false;
        if (!empty($booking->user) && $request->notify_user) {
            $shouldSendEmail = true;
        }
        /* @var Flight $flight */
        $flight = $booking->flights()->first();
        $booking->fill([
            'is_editable' => $request->is_editable,
            'callsign' => $request->callsign,
            'acType' => $request->aircraft,
            'final_information_email_sent_at' => null
        ]);

        $flightAttributes = [
            'dep' => $request->dep,
            'arr' => $request->arr,
            'route' => $request->route,
            'oceanicFL' => $request->oceanicFL,
            'oceanicTrack' => $request->oceanicTrack,
            'notes' => $request->notes,
        ];

        if ($request->ctot) {
            $flightAttributes['ctot'] = Carbon::createFromFormat('Y-m-d H:i',
                $booking->event->startEvent->toDateString().' '.$request->ctot);
        }

        if ($request->eta) {
            $flightAttributes['eta'] = Carbon::createFromFormat('Y-m-d H:i',
                $booking->event->startEvent->toDateString().' '.$request->eta);
        }

        $flight->fill($flightAttributes);

        if ($shouldSendEmail) {
            $changes = collect();
            foreach ($booking->getDirty() as $key => $value) {
                $changes->push(
                    ['name' => $key, 'old' => $booking->getOriginal($key), 'new' => $value]
                );
            }
            foreach ($flight->getDirty() as $key => $value) {
                $changes->push(
                    ['name' => $key, 'old' => $flight->getOriginal($key), 'new' => $value]
                );
            }
            if (!empty($request->message)) {
                $changes->push(
                    ['name' => 'message', 'new' => $request->message]
                );
            }
        }

        $booking->save();
        $flight->save();
        $message = 'Booking has been changed!';
        if ($shouldSendEmail) {
            event(new BookingChanged($booking, $changes));
            $message .= ' A E-mail has also been sent to the person that booked.';
        }
        flashMessage('success', 'Booking changed', $message);
        return redirect(route('bookings.event.index', $booking->event));
    }

    /**
     * Remove the specified booking from storage.
     *
     * @param  Booking  $booking
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function destroy(Booking $booking)
    {
        if ($booking->event->endEvent >= now()) {
            $message = 'Booking has been deleted.';
            if (!empty($booking->user)) {
                $message .= ' A E-mail has also been sent to the person that booked.';
                event(new BookingDeleted($booking));
            }
            $booking->delete();
            flashMessage('success', 'Booking deleted!', $message);
            return redirect(route('bookings.event.index', $booking->event));
        }
        flashMessage('danger', 'Nope!', 'You cannot delete a booking after the event ended');
        return back();
    }

    /**
     * Exports all active bookings to a .csv file
     *
     * @param  Event  $event
     * @return string|\Symfony\Component\HttpFoundation\StreamedResponse
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\InvalidArgumentException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function export(Event $event, Request $request)
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Export triggered');
        $bookings = Booking::where('event_id', $event->id)
            ->where('status', BookingStatus::BOOKED)
            ->get();
        if ($event->event_type_id == EventType::MULTIFLIGHTS) {
            if ($request->vacc) {
                return (new FastExcel($bookings))->withoutHeaders()->download('bookings.csv', function ($booking) {
                    /* @var Booking $booking */
                    /* @var Flight $flight1 */
                    /* @var Flight $flight2 */
                    $flight1 = $booking->flights()->first();
                    $flight2 = $booking->flights()->whereKeyNot($flight1->id)->first();
                    return [
                        $booking->user->full_name,
                        $booking->user_id,
                        $booking->user->email,
                        $booking->callsign,
                        $flight1->airportDep->icao,
                        $flight2->airportDep->icao,
                        $flight2->airportArr->icao,
                    ];
                });
            }
            return (new FastExcel($bookings))->withoutHeaders()->download('bookings.csv', function ($booking) {
                /* @var Booking $booking */
                /* @var Flight $flight1 */
                /* @var Flight $flight2 */
                $flight1 = $booking->flights()->first();
                $flight2 = $booking->flights()->whereKeyNot($flight1->id)->first();
                return [
                    $booking->user->full_name,
                    $booking->user_id,
                    $booking->callsign,
                    $flight1->airportDep->icao,
                    Carbon::parse($flight1->getOriginal('ctot'))->format('H:i:s'),
                    $flight2->airportDep->icao,
                    Carbon::parse($flight2->getOriginal('ctot'))->format('H:i:s'),
                    $flight2->airportArr->icao,
                ];
            });
        } else {
            return (new FastExcel($bookings))->withoutHeaders()->download('bookings.csv', function ($booking) {
                /* @var Booking $booking */
                /* @var Flight $flight */
                $flight = $booking->flights()->first();
                if (!empty($flight->getOriginal('ctot'))) {
                    $ctot = Carbon::parse($flight->getOriginal('ctot'))->format('H:i:s');
                } else {
                    $ctot = null;
                }
                if (!empty($flight->getOriginal('eta'))) {
                    $eta = Carbon::parse($flight->getOriginal('eta'))->format('H:i:s');
                } else {
                    $eta = null;
                }
                return [
                    $booking->user->full_name,
                    $booking->user_id,
                    $booking->callsign,
                    $booking->acType,
                    $flight->airportDep->icao,
                    $flight->airportArr->icao,
                    $flight->getOriginal('oceanicFL'),
                    $ctot,
                    $eta,
                    $flight->route,
                ];
            });
        }
    }

    public function importForm(Event $event)
    {
        return view('event.admin.import', compact('event'));
    }

    public function import(ImportBookings $request, Event $event)
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Import triggered');
        $file = $request->file('file')->getRealPath();
        if ($event->event_type_id == EventType::MULTIFLIGHTS) {
            (new FastExcel)->importSheets($file, function ($line) use ($event) {
               $airport1 = Airport::where('icao', $line['Airport 1'])->first();
               $airport2 = Airport::where('icao', $line['Airport 2'])->first();
               $airport3 = Airport::where('icao', $line['Airport 3'])->first();

               if (isset($line['CTOT 1'])) {
                   $ctot1 = Carbon::createFromFormat('Y-m-d H:i',
                       $event->startEvent->toDateString().' '.$line['CTOT 1']->format('H:i'));
               }
                if (isset($line['CTOT 2'])) {
                    $ctot2 = Carbon::createFromFormat('Y-m-d H:i',
                       $event->startEvent->toDateString().' '.$line['CTOT 2']->format('H:i'));
                }

                $booking = Booking::create([
                    'event_id' => $event->id,
                    'is_editable' => true,
                ]);

                $booking->flights()->createMany([
                    [
                        'order_by' => 1,
                        'dep' => $airport1->id,
                        'arr' => $airport2->id,
                        'ctot' => $ctot1,
                    ],
                    [
                        'order_by' => 2,
                        'dep' => $airport2->id,
                        'arr' => $airport3->id,
                        'ctot' => $ctot2,
                    ],
                ]);
            });
        } else {
            $success = true;
            (new FastExcel)->importSheets($file, function ($line) use ($success, $event) {
                if (!$success) {
                    return false;
                }
                $editable = true;
                $dep = Airport::where('icao', $line['Origin'])->first();
                if (!$dep) {
                    flashMessage('danger', 'Airport ' . $line['Origin'] . ' does not exist', 'Add the airport, then try again');
                    $success = false;
                }
                $arr = Airport::where('icao', $line['Destination'])->first();
                if (!$arr) {
                    flashMessage('danger', 'Airport ' . $line['Destination'] . ' does not exist', 'Add the airport, then try again');
                    $success = false;
                }

                if (!$success) {
                    return false;
                }

                $booking = new Booking();
                if (!empty($line['Call Sign']) && !empty($line['Aircraft Type'])) {
                    $editable = false;
                    $booking->fill([
                        'callsign' => $line['Call Sign'],
                        'acType' => $line['Aircraft Type'],

                    ]);
                }
                $booking->fill([
                    'event_id' => $event->id,
                    'is_editable' => $editable,
                ])->save();

                $flight = collect([
                    'dep' => $dep->id,
                    'arr' => $arr->id,
                    'notes' => $line['Notes'] ?? null,
                ]);
                if (!empty($line['ETA'])) {
                    $flight->put('eta', Carbon::createFromFormat('Y-m-d H:i',
                        $event->startEvent->toDateString().' '.$line['ETA']->format('H:i')));
                }
                if (!empty($line['EOBT'])) {
                    $flight->put('ctot', Carbon::createFromFormat('Y-m-d H:i',
                        $event->startEvent->toDateString().' '.$line['EOBT']->format('H:i')));
                }
                if (!empty($line['Route'])) {
                    $flight->put('route', $line['Route']);
                }
                $booking->flights()->create($flight->toArray());
            });
        }
        Storage::delete($file);
        flashMessage('success', 'Flights imported', 'Flights have been imported');
        return redirect(route('bookings.event.index', $event));
    }

    /**
     * Show the form for editing the specified booking.
     *
     * @param  Event  $event
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function adminAutoAssignForm(Event $event)
    {
        return view('event.admin.autoAssign', compact('event'));
    }

    /**
     * @param  AutoAssign  $request
     * @param  Event  $event
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function adminAutoAssign(AutoAssign $request, Event $event)
    {
        // @TODO Optimise this, for now it's a ugly fix
        $bookings = $event->bookings()
        ->with(['flights' => function ($query) {
            $query->orderBy('ctot');
        }]);

        if (!$request->checkAssignAllFlights) {
            $bookings = $bookings->where('status', BookingStatus::BOOKED);
        }
        $bookings = $bookings->get();
        $count = 0;
        $flOdd = $request->maxFL;
        $flEven = $request->minFL;
        foreach ($bookings as $booking) {
            $flight = $booking->flights()->first();
            $count++;
            if ($count % 2 == 0) {
                $flight->fill([
                    'oceanicTrack' => $request->oceanicTrack2,
                    'route' => $request->route2,
                    'oceanicFL' => $flEven,
                ]);
                $flEven = $flEven + 10;
                if ($flEven > $request->maxFL) {
                    $flEven = $request->minFL;
                }
            } else {
                $flight->fill([
                    'oceanicTrack' => $request->oceanicTrack1,
                    'route' => $request->route1,
                    'oceanicFL' => $flOdd,
                ]);
                $flOdd = $flOdd - 10;
                if ($flOdd < $request->minFL) {
                    $flOdd = $request->maxFL;
                }
            }
            $flight->save();

        }
        flashMessage('success', 'Bookings changed', $count.' Bookings have been Auto-Assigned a FL, and route');
        activity()
            ->by(auth()->user())
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

    public function routeAssignForm(Event $event)
    {
        return view('event.admin.routeAssign', compact('event'));
    }


    public function routeAssign(RouteAssign $request, Event $event)
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Route assign triggered');
        $file = $request->file('file')->getRealPath();
        //From,To,Route,Notes
        (new FastExcel)->importSheets($file, function ($line) use ($event) {
            $from = Airport::where('icao', $line['From'])->first();
            $to = Airport::where('icao', $line['To'])->first();
            $notes = $line['Notes'] ?? null;
            Flight::whereDep($from->id)
                ->whereArr($to->id)
                ->update([
                'route' => $line['Route'],
                'notes' => $notes
            ]);

        });
        Storage::delete($file);
        flashMessage('success', 'Routes assigned', 'Routes have been assigned to flights');
        return redirect(route('bookings.event.index', $event));
    }
}
