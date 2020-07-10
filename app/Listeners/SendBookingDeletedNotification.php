<?php

namespace App\Listeners;

use App\Events\BookingDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBookingDeletedNotification implements ShouldQueue
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
     * @param  BookingDeleted  $event
     * @return void
     */
    public function handle(BookingDeleted $event)
    {
        $event->booking->user->notify(new \App\Notifications\BookingDeleted($event->booking->event));
    }
}
