<?php

namespace App\Listeners;

use App\Events\BookingConfirmed;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingConfirmedNotification implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  BookingConfirmed  $event
     * @return void
     */
    public function handle(BookingConfirmed $event)
    {
        activity()
            ->by(auth()->user())
            ->on($event->booking)
            ->log('Flight booked');

        $event->booking->user->notify(new \App\Notifications\BookingConfirmed($event->booking));
    }
}
