<?php

namespace App\Actions\Booking;

use App\Http\Requests\Booking\Admin\StoreBooking;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use Illuminate\Support\Facades\Date;

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
        $eventStart = Date::createFromFormat(
            'Y-m-d H:i',
            $event->startEvent->toDateString() . ' ' . $request->start
        );
        $eventEnd = Date::createFromFormat(
            'Y-m-d H:i',
            $event->endEvent->toDateString() . ' ' . $request->end
        );
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
                    'is_editable' => $request->is_editable,
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
            'notes' => $request->notes,
        ];

        if ($request->ctot) {
            $flightAttributes['ctot'] = Date::createFromFormat(
                'Y-m-d H:i',
                $event->startEvent->toDateString() . ' ' . $request->ctot
            );
        }

        if ($request->eta) {
            $flightAttributes['eta'] = Date::createFromFormat(
                'Y-m-d H:i',
                $event->startEvent->toDateString() . ' ' . $request->eta
            );
        }

        $booking->flights()->create($flightAttributes);
    }
}
