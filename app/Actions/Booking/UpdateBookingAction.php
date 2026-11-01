<?php

namespace App\Actions\Booking;

use App\Events\BookingChanged;
use App\Http\Requests\Booking\Admin\UpdateBooking;
use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;

class UpdateBookingAction
{
    /**
     * Update a booking and its flight, optionally notifying the assigned user.
     */
    public function handle(UpdateBooking $request, Booking $booking): void
    {
        $shouldSendEmail = $booking->user_id && $request->notify_user;

        /** @var Flight $flight */
        $flight = $booking->flights()->first();

        $booking->fill([
            'is_callsign_editable' => $request->is_callsign_editable,
            'is_actype_editable' => $request->is_actype_editable,
            'callsign' => $request->callsign,
            'acType' => $request->acType,
            'final_information_email_sent_at' => null,
        ]);

        $event = $booking->event;
        $ctot = $request->ctot ? $event->resolveSlotTime($request->ctot) : null;

        $flightAttributes = [
            'dep' => $request->dep,
            'arr' => $request->arr,
            'route' => $request->route,
            'oceanicFL' => $request->oceanicFL,
            'oceanicTrack' => $request->oceanicTrack,
            'notes' => $request->notes,
            'ctot' => $ctot,
            'eta' => $request->eta
                ? ($ctot
                    ? $event->resolveTimeAfter($request->eta, $ctot)
                    : $event->resolveSlotTime($request->eta))
                : null,
        ];

        $flight->fill($flightAttributes);

        if ($shouldSendEmail) {
            $changes = collect();

            $notifiableBookingChanges = Arr::except($booking->getDirty(), [
                'is_callsign_editable',
                'is_actype_editable',
                'final_information_email_sent_at',
            ]);

            foreach ($notifiableBookingChanges as $key => $value) {
                $changes->push(
                    ['name' => $key, 'old' => $booking->getOriginal($key), 'new' => $value]
                );
            }

            foreach ($flight->getDirty() as $key => $value) {
                $old = $flight->getOriginal($key);

                if (in_array($key, ['ctot', 'eta'], true) && $this->hasSameTimeOfDay($old, $value)) {
                    continue;
                }

                $changes->push(
                    ['name' => $key, 'old' => $old, 'new' => $value]
                );
            }

            if (! empty($request->message)) {
                $changes->push(
                    ['name' => 'message', 'new' => $request->message]
                );
            }
        }

        $booking->save();
        $flight->save();

        if ($shouldSendEmail && $changes->isNotEmpty()) {
            event(new BookingChanged($booking, $changes));
        }
    }

    /**
     * Pilots only see the time of day, so a date-only correction is not a change to them.
     */
    private function hasSameTimeOfDay(mixed $old, mixed $new): bool
    {
        return filled($old)
            && filled($new)
            && Date::parse($old)->format('Hi') === Date::parse($new)->format('Hi');
    }
}
