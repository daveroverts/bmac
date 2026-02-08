<?php

namespace App\Jobs;

use App\Models\Event;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class EventCleanupReservationsJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Event $event)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->event->bookings()
            ->whereStatus(BookingStatus::RESERVED->value)
            ->each(function (Booking $booking): void {
                if (now()->greaterThanOrEqualTo($booking->updated_at->addMinutes(10))) {
                    $booking->status = BookingStatus::UNASSIGNED;
                    $booking->user_id = null;
                    $booking->save();
                }
            });
    }
}
