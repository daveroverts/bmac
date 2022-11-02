<?php

namespace App\Listeners;

use App\Events\BookingChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingChangedNotification implements ShouldQueue
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
     * @param  BookingChanged  $event
     * @return void
     */
    public function handle(BookingChanged $event)
    {
        $event->booking->user->notify(new \App\Notifications\BookingChanged($event->booking, $event->changes));
    }
}
