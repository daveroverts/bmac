<?php

namespace App\Actions\Booking;

use App\Events\BookingChanged;
use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class BulkUpdateBookingsAction
{
    /**
     * Apply the given values to every booking. Keys left out stay unchanged, and a null value clears the field.
     *
     * @param  Collection<int, Booking>  $bookings
     * @param  array<string, mixed>  $values  Booking and flight attributes, plus an optional timeShift in minutes.
     * @return int The number of pilots that could not be notified.
     */
    public function handle(Collection $bookings, array $values, bool $notifyUsers, ?string $message = null): int
    {
        $bookingChanges = [];

        DB::transaction(function () use ($bookings, $values, $notifyUsers, $message, &$bookingChanges): void {
            foreach ($bookings as $booking) {
                $changes = $this->updateBooking($booking, $values);

                if ($notifyUsers && $booking->user_id && $changes->isNotEmpty()) {
                    if (filled($message)) {
                        $changes->push(['name' => 'message', 'new' => $message]);
                    }

                    $bookingChanges[] = [$booking, $changes];
                }
            }
        });

        $failedNotifications = 0;

        foreach ($bookingChanges as [$booking, $changes]) {
            try {
                event(new BookingChanged($booking, $changes));
            } catch (Throwable $throwable) {
                report($throwable);
                $failedNotifications++;
            }
        }

        return $failedNotifications;
    }

    /**
     * @param  array<string, mixed>  $values
     * @return \Illuminate\Support\Collection<int, array{name: string, old?: mixed, new: mixed, flight?: int}>
     */
    private function updateBooking(Booking $booking, array $values): \Illuminate\Support\Collection
    {
        $changes = collect();

        $booking->fill(Arr::only($values, ['acType', 'is_callsign_editable', 'is_actype_editable']));

        if ($booking->isDirty('acType')) {
            $changes->push(['name' => 'acType', 'old' => $booking->getOriginal('acType'), 'new' => $booking->acType]);
        }

        /** @var Flight $flight */
        foreach ($booking->flights as $flight) {
            $flight->fill(Arr::only($values, ['route', 'oceanicFL', 'notes']));

            if (isset($values['timeShift'])) {
                $flight->ctot = $flight->ctot?->copy()->addMinutes($values['timeShift']);
                $flight->eta = $flight->eta?->copy()->addMinutes($values['timeShift']);
            }

            foreach (array_keys($flight->getDirty()) as $key) {
                $change = ['name' => $key, 'old' => $flight->getOriginal($key), 'new' => $flight->getAttribute($key)];

                if ($booking->flights->count() > 1) {
                    $change['flight'] = (int) $flight->order_by;
                }

                $changes->push($change);
            }

            $flight->save();
        }

        if ($changes->isNotEmpty()) {
            $booking->final_information_email_sent_at = null;
        }

        $booking->save();

        return $changes;
    }
}
