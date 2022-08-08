<?php

namespace App\Http\Controllers\Booking;

use Carbon\Carbon;
use App\Models\Event;
use App\Models\Flight;
use App\Models\Airport;
use App\Models\Booking;
use Illuminate\View\View;
use App\Enums\BookingStatus;
use Illuminate\Http\Request;
use App\Events\BookingChanged;
use App\Events\BookingDeleted;
use App\Exports\BookingsExport;
use App\Imports\BookingsImport;
use App\Policies\BookingPolicy;
use App\Imports\FlightRouteAssign;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\AdminController;
use App\Http\Requests\Booking\Admin\AutoAssign;
use App\Http\Requests\Booking\Admin\RouteAssign;
use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Http\Requests\Booking\Admin\UpdateBooking;
use App\Http\Requests\Booking\Admin\ImportBookings;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BookingAdminController extends AdminController
{
    public function __construct()
    {
        $this->authorizeResource(BookingPolicy::class, 'booking');
    }

    public function create(Event $event, Request $request): View
    {
        $bulk = $request->bulk;
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(function ($airport) {
                /** @var Airport $airport */
                return "$airport->icao | $airport->name | $airport->iata";
            });

        return view('booking.admin.create', compact('event', 'airports', 'bulk'));
    }
    public function unConfirmRemove(Event $event)
    {
        $bookings = $event->bookings->where('confirmed_at',NULL)->where('status',2);
        foreach($bookings as $booking){
        $booking->fill([
        'callsign' => null,
        'acType' => null,
        'selcal' => null,
        'confirmed_at' => null,])->save();
        $booking->status = BookingStatus::UNASSIGNED;
        $booking->user()->dissociate()->save();
        }
        return to_route('bookings.event.index', $event);
    }
    public function store(StoreBooking $request): RedirectResponse
    {
        $event = Event::whereKey($request->id)->first();
        if ($request->bulk) {
            $event_start = Carbon::createFromFormat(
                'Y-m-d H:i',
                $event->startEvent->toDateString() . ' ' . $request->start
            );
            $event_end = Carbon::createFromFormat('Y-m-d H:i', $event->endEvent->toDateString() . ' ' . $request->end);
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
            flashMessage('success', __('Done'), __(':count slots have been created!', ['count' => $count]));
        } else {
            $booking = new Booking([
                'is_editable' => $request->is_editable,
                'callsign' => $request->callsign,
                'acType' => $request->acType,
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
                $flightAttributes['ctot'] = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $event->startEvent->toDateString() . ' ' . $request->ctot
                );
            }

            if ($request->eta) {
                $flightAttributes['eta'] = Carbon::createFromFormat(
                    'Y-m-d H:i',
                    $event->startEvent->toDateString() . ' ' . $request->eta
                );
            }

            $booking->flights()->create($flightAttributes);
            flashMessage('success', __('Done'), __('Slot created'));
        }
        return to_route('bookings.event.index', $event);
    }

    public function show(Booking $booking): View
    {
        return view('booking.show', compact('booking'));
    }

    public function edit(Booking $booking): View|RedirectResponse
    {
        if ($booking->event->endEvent >= now()) {
            $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
                ->map(function ($airport) {
                    /** @var Airport $airport */
                    return "$airport->icao | $airport->name | $airport->iata";
                });
            $flight = $booking->flights()->first();
            return view('booking.admin.edit', compact('booking', 'airports', 'flight'));
        }
        flashMessage('danger', __('Danger'), __('Booking can no longer be edited'));
        return back();
    }

    public function update(UpdateBooking $request, Booking $booking): RedirectResponse
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
            'acType' => $request->acType,
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
            $flightAttributes['ctot'] = Carbon::createFromFormat(
                'Y-m-d H:i',
                $booking->event->startEvent->toDateString() . ' ' . $request->ctot
            );
        }

        if ($request->eta) {
            $flightAttributes['eta'] = Carbon::createFromFormat(
                'Y-m-d H:i',
                $booking->event->startEvent->toDateString() . ' ' . $request->eta
            );
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
        if ($shouldSendEmail) {
            event(new BookingChanged($booking, $changes));
        }
        flashMessage('success', 'Booking changed', __('Booking has been changed!'));
        return to_route('bookings.event.index', $booking->event);
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        if ($booking->event->endEvent >= now()) {
            if (!empty($booking->user)) {
                event(new BookingDeleted($booking->event, $booking->user));
            }
            $booking->delete();
            flashMessage('success', 'Booking deleted!', __('Booking has been deleted.'));
            return to_route('bookings.event.index', $booking->event);
        }
        flashMessage('danger', __('Danger'), __('Booking can no longer be deleted'));
        return back();
    }

    public function export(Event $event, Request $request): BinaryFileResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Export triggered');

        return (new BookingsExport($event, $request->vacc))->download('bookings.csv');
    }

    public function importForm(Event $event)
    {
        return view('event.admin.import', compact('event'));
    }

    public function import(ImportBookings $request, Event $event): RedirectResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Import triggered');
        $file = $request->file('file');
        (new BookingsImport($event))->import($file);
        Storage::delete($file->getRealPath());
        flashMessage('success', __('Flights imported'), __('Flights have been imported'));
        return to_route('bookings.event.index', $event);
    }

    public function adminAutoAssignForm(Event $event): View
    {
        return view('event.admin.autoAssign', compact('event'));
    }

    public function adminAutoAssign(AutoAssign $request, Event $event): RedirectResponse
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
        flashMessage('success', __('Bookings changed'), __(':count bookings have been Auto-Assigned a FL, and route', ['count' => $count]));
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
        return to_route('admin.events.index');
    }

    public function routeAssignForm(Event $event): View
    {
        return view('event.admin.routeAssign', compact('event'));
    }


    public function routeAssign(RouteAssign $request, Event $event): RedirectResponse
    {
        activity()
            ->by(auth()->user())
            ->on($event)
            ->log('Route assign triggered');
        $file = $request->file('file');
        (new FlightRouteAssign())->import($file);
        Storage::delete($file);
        flashMessage('success', __('Routes assigned'), __('Routes have been assigned to flights'));
        return to_route('bookings.event.index', $event);
    }
}
