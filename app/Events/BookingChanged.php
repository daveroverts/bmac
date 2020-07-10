<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class BookingChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $booking;
    public $changes;

    /**
     * Create a new event instance.
     *
     * @param Booking $booking
     * @param Collection $changes
     *
     * @return void
     */
    public function __construct(Booking $booking, Collection $changes)
    {
        $this->booking = $booking;
        $this->changes = $changes;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
