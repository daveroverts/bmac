<?php

namespace App\Actions\Booking;

use App\Events\BookingDeleted;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

class DeleteBookingsAction
{
    /**
     * Delete the given bookings of an event and send each affected pilot one grouped e-mail.
     *
     * @param  Collection<int, Booking>  $bookings
     * @return int The number of pilots that could not be notified.
     */
    public function handle(Event $event, Collection $bookings): int
    {
        $bookings->loadMissing('user');
        $users = $bookings->whereNotNull('user_id')->pluck('user')->unique('id')->values();

        DB::transaction(function () use ($event, $bookings): void {
            $bookings->each->delete();

            activity()
                ->by(auth()->user())
                ->on($event)
                ->withProperties(['count' => $bookings->count(), 'bookings' => $bookings->modelKeys()])
                ->log('Delete selected bookings');
        });

        $failedNotifications = 0;

        foreach ($users as $user) {
            $callsigns = $bookings->where('user_id', $user->id)->map(fn (Booking $booking): string => $booking->formatted_callsign)->values()->all();

            try {
                event(new BookingDeleted($event, $user, $callsigns));
            } catch (Throwable $throwable) {
                report($throwable);
                $failedNotifications++;
            }
        }

        return $failedNotifications;
    }
}
