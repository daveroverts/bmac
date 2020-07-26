<?php

namespace App\Listeners;

use App\Events\EventFinalInformation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

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
        if ($event->testMode) {
            activity()
                ->by(auth()->user())
                ->on($event->booking)
                ->withProperties(
                    [
                        'booking' => $event->booking,
                    ]
                )
                ->log('Final Information E-mail test performed');
        }  else {
            $event->booking->update(['final_information_email_sent_at' => now()]);
        }
        $event->booking->user->notify(new \App\Notifications\EventFinalInformation($event->booking));
    }
}
