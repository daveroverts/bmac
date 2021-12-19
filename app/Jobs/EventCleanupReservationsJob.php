<?php

namespace App\Jobs;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EventCleanupReservationsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Event $event)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->event->bookings()
            ->whereStatus(BookingStatus::RESERVED)
            ->each(function (Booking $booking) {
                if (now()->greaterThanOrEqualTo($booking->updated_at->addMinutes(10))) {
                    $booking->status = BookingStatus::UNASSIGNED;
                    $booking->user_id = null;
                    $booking->save();
                }
            });
    }
}
