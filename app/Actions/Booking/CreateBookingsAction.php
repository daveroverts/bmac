<?php

namespace App\Actions\Booking;

use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;

class CreateBookingsAction
{
    /**
     * Create booking slot(s) for the given event.
     */
    public function handle(StoreBooking $request, Event $event): int
    {
        if ($request->bulk) {
            return $this->createBulk($request, $event);
        }

        $this->createSingle($request, $event);

        return 1;
    }

    /**
     * Generate time-separated bulk booking slots.
     */
    protected function createBulk(StoreBooking $request, Event $event): int
    {
        $eventStart = $event->resolveSlotTime($request->start);
        $eventEnd = $event->resolveTimeAfter($request->end, $eventStart);
        $separation = $request->separation * 60;
        $count = 0;

        for (; $eventStart <= $eventEnd; $eventStart->addSeconds($separation)) {
            $time = $eventStart->copy();
            if ($time->second >= 30) {
                $time->addMinute();
            }

            $time->second = 0;

            $duplicateExists = Flight::whereHas('booking', function ($query) use ($request): void {
                $query->where('event_id', $request->id);
            })->where([
                'ctot' => $time,
                'dep' => $request->dep,
            ])->exists();

            if (! $duplicateExists) {
                Booking::create([
                    'event_id' => $request->id,
                    'is_callsign_editable' => $request->is_callsign_editable,
                    'is_actype_editable' => $request->is_actype_editable,
                ])->flights()->create([
                    'dep' => $request->dep,
                    'arr' => $request->arr,
                    'ctot' => $time,
                    'notes' => $request->notes,
                ]);

                $count++;
            }
        }

        return $count;
    }

    /**
     * Create a single booking with its flight.
     */
    protected function createSingle(StoreBooking $request, Event $event): void
    {
        $booking = new Booking([
            'is_callsign_editable' => $request->is_callsign_editable,
            'is_actype_editable' => $request->is_actype_editable,
            'callsign' => $request->callsign,
            'acType' => $request->acType,
        ]);

        $booking->event()->associate($request->id)->save();

        $flightAttributes = [
            'dep' => $request->dep,
            'arr' => $request->arr,
            'route' => $request->route,
            'oceanicFL' => $request->oceanicFL,
            'notes' => $request->notes,
        ];

        $ctot = $request->ctot ? $event->resolveSlotTime($request->ctot) : null;

        if ($ctot instanceof \Illuminate\Support\Carbon) {
            $flightAttributes['ctot'] = $ctot;
        }

        if ($request->eta) {
            $flightAttributes['eta'] = $ctot instanceof \Illuminate\Support\Carbon
                ? $event->resolveTimeAfter($request->eta, $ctot)
                : $event->resolveSlotTime($request->eta);
        }

        $booking->flights()->create($flightAttributes);
    }
}
