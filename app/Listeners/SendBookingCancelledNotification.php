<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendBookingCancelledNotification implements ShouldQueue
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
     * @param  BookingCancelled $event
     *
     * @return void
     */
    public function handle(BookingCancelled $event)
    {
        activity()
            ->by($event->user)
            ->on($event->booking)
            ->log('Flight available');
        $event->user->notify(new \App\Notifications\BookingCancelled($event->booking->event));
    }
}
