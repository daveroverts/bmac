<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingConfirmedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingConfirmed $event): void
    {
        activity()
            ->by(auth()->user())
            ->on($event->booking)
            ->log('Flight booked');

        $event->booking->user->notify(new \App\Notifications\BookingConfirmed($event->booking));
    }
}
