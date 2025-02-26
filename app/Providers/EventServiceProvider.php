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
        \App\Events\BookingConfirmed::class => [
            \App\Listeners\SendBookingConfirmedNotification::class,
        ],
        \App\Events\BookingCancelled::class => [
            \App\Listeners\SendBookingCancelledNotification::class,
        ],
        \App\Events\BookingChanged::class => [
            \App\Listeners\SendBookingChangedNotification::class,
        ],
        \App\Events\BookingDeleted::class => [
            \App\Listeners\SendBookingDeletedNotification::class,
        ],
        \App\Events\EventBulkEmail::class => [
            \App\Listeners\SendEventBulkEmailNotification::class,
        ],
        \App\Events\EventFinalInformation::class => [
            \App\Listeners\SendEventFinalInformationNotification::class,
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot(): void
    {
        parent::boot();

        //
    }
}
