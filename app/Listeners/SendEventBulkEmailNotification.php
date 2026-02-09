<?php

namespace App\Listeners;

use App\Events\EventBulkEmail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class SendEventBulkEmailNotification implements ShouldQueue
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
    public function handle(EventBulkEmail $event): void
    {
        $log = isset($event->request['testmode']) ? 'Bulk E-mail test performed' : 'Bulk E-mail';
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
