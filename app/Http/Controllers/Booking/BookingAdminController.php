<?php

namespace App\Http\Controllers\Booking;

use App\Models\Event;
use App\Models\Flight;
use App\Models\Airport;
use App\Models\Booking;
use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Events\BookingChanged;
use App\Events\BookingDeleted;
use App\Policies\BookingPolicy;
use Illuminate\Http\RedirectResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Http\Requests\Booking\Admin\UpdateBooking;

class BookingAdminController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(BookingPolicy::class, 'booking');
    }

    public function create(Event $event, Request $request): View
    {
        $bulk = $request->bulk;
        $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
            ->map(fn ($airport): string =>
                /** @var Airport $airport */
                sprintf('%s | %s | %s', $airport->icao, $airport->name, $airport->iata));

        return view('booking.admin.create', ['event' => $event, 'airports' => $airports, 'bulk' => $bulk]);
    }

    public function store(StoreBooking $request): RedirectResponse
    {
        $event = Event::whereKey($request->id)->first();
        if ($request->bulk) {
            $event_start = \Illuminate\Support\Facades\Date::createFromFormat(
                'Y-m-d H:i',
                $event->startEvent->toDateString() . ' ' . $request->start
            );
            $event_end = \Illuminate\Support\Facades\Date::createFromFormat('Y-m-d H:i', $event->endEvent->toDateString() . ' ' . $request->end);
            $separation = $request->separation * 60;
            $count = 0;
            for (; $event_start <= $event_end; $event_start->addSeconds($separation)) {
                $time = $event_start->copy();
                if ($time->second >= 30) {
                    $time->addMinute();
                }

                $time->second = 0;

                if (!Flight::whereHas('booking', function ($query) use ($request): void {
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
                $flightAttributes['ctot'] = \Illuminate\Support\Facades\Date::createFromFormat(
                    'Y-m-d H:i',
                    $event->startEvent->toDateString() . ' ' . $request->ctot
                );
            }

            if ($request->eta) {
                $flightAttributes['eta'] = \Illuminate\Support\Facades\Date::createFromFormat(
                    'Y-m-d H:i',
                    $event->startEvent->toDateString() . ' ' . $request->eta
                );
            }

            $booking->flights()->create($flightAttributes);
            flashMessage('success', __('Done'), __('Slot created'));
        }

        return to_route('bookings.event.index', $event);
    }

    public function edit(Booking $booking): View|RedirectResponse
    {
        if ($booking->event->endEvent >= now()) {
            $airports = Airport::all(['id', 'icao', 'iata', 'name'])->keyBy('id')
                ->map(fn ($airport): string =>
                    /** @var Airport $airport */
                    sprintf('%s | %s | %s', $airport->icao, $airport->name, $airport->iata));
            $flight = $booking->flights()->first();
            return view('booking.admin.edit', ['booking' => $booking, 'airports' => $airports, 'flight' => $flight]);
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
            $flightAttributes['ctot'] = \Illuminate\Support\Facades\Date::createFromFormat(
                'Y-m-d H:i',
                $booking->event->startEvent->toDateString() . ' ' . $request->ctot
            );
        } else {
            $flightAttributes['ctot'] = null;
        }

        if ($request->eta) {
            $flightAttributes['eta'] = \Illuminate\Support\Facades\Date::createFromFormat(
                'Y-m-d H:i',
                $booking->event->startEvent->toDateString() . ' ' . $request->eta
            );
        } else {
            $flightAttributes['eta'] = null;
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
            if ($booking->user_id) {
                event(new BookingDeleted($booking->event, $booking->user));
            }

            $booking->delete();
            flashMessage('success', 'Booking deleted!', __('Booking has been deleted.'));
            return to_route('bookings.event.index', $booking->event);
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
