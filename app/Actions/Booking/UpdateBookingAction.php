<?php

namespace App\Actions\Booking;

use App\Events\BookingChanged;
use App\Http\Requests\Booking\Admin\UpdateBooking;
use App\Models\Booking;
use App\Models\Flight;
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
            'is_editable' => $request->is_editable,
            'callsign' => $request->callsign,
            'acType' => $request->acType,
            'final_information_email_sent_at' => null,
        ]);

        $flightAttributes = [
            'dep' => $request->dep,
            'arr' => $request->arr,
            'route' => $request->route,
            'oceanicFL' => $request->oceanicFL,
            'oceanicTrack' => $request->oceanicTrack,
            'notes' => $request->notes,
            'ctot' => $request->ctot
                ? Date::createFromFormat('Y-m-d H:i', $booking->event->startEvent->toDateString() . ' ' . $request->ctot)
                : null,
            'eta' => $request->eta
                ? Date::createFromFormat('Y-m-d H:i', $booking->event->startEvent->toDateString() . ' ' . $request->eta)
                : null,
        ];

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

            if (! empty($request->message)) {
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
    }
}
