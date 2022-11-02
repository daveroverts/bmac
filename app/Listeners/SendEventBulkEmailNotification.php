<?php

namespace App\Listeners;

use App\Events\EventBulkEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendEventBulkEmailNotification implements ShouldQueue
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
     * @param  EventBulkEmail  $event
     * @return void
     */
    public function handle(EventBulkEmail $event)
    {
        if (isset($event->request['testmode'])) {
            $log = 'Bulk E-mail test performed';
        } else {
            $log = 'Bulk E-mail';
        }
        activity()
            ->by(auth()->user())
            ->on($event->event)
            ->withProperties(
                [
                    'subject' => $event->request['subject'],
                    'message' => $event->request['message'],
                ]
            )
            ->log($log);

        Notification::send($event->users, new \App\Notifications\EventBulkEmail($event->event, $event->request['subject'], $event->request['message']));
    }
}
