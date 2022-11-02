<?php

namespace App\Listeners;

use App\Events\EventFinalInformation;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEventFinalInformationNotification implements ShouldQueue
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
     * @param  EventFinalInformation  $event
     * @return void
     */
    public function handle(EventFinalInformation $event)
    {
        if ($event->testUser) {
            activity()
                ->by(auth()->user())
                ->on($event->booking)
                ->withProperties(
                    [
                        'booking' => $event->booking,
                    ]
                )
                ->log('Final Information E-mail test performed');

            $event->testUser->notify(new \App\Notifications\EventFinalInformation($event->booking));
            return;
        }

        $event->booking->update(['final_information_email_sent_at' => now()]);
        $event->booking->user->notify(new \App\Notifications\EventFinalInformation($event->booking));
    }
}
