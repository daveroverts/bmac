<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\BookingConfirmed' => [
            'App\Listeners\SendBookingConfirmedNotification',
        ],
        'App\Events\BookingCancelled' => [
            'App\Listeners\SendBookingCancelledNotification',
        ],
        'App\Events\BookingChanged' => [
            'App\Listeners\SendBookingChangedNotification',
        ],
        'App\Events\BookingDeleted' => [
            'App\Listeners\SendBookingDeletedNotification',
        ],
        'App\Events\EventBulkEmail' => [
            'App\Listeners\SendEventBulkEmailNotification',
        ],
        'App\Events\EventFinalInformation' => [
            'App\Listeners\SendEventFinalInformationNotification',
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
