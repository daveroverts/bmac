<?php

namespace App\Listeners;

use App\Events\BookingDeleted;
use Illuminate\Contracts\Queue\ShouldQueue;

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
     */
    public function handle(BookingDeleted $event): void
    {
        $event->user->notify(new \App\Notifications\BookingDeleted($event->event));
    }
}
