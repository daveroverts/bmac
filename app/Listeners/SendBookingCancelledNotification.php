<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
            ->by(auth()->user())
            ->on($event->booking)
            ->log('Flight available');
        $event->booking->user->notify(new \App\Notifications\BookingCancelled($event->booking->event));
    }
}
