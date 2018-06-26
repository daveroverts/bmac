<?php

namespace App\Mail;

use App\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class BookingChanged extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The booking instance.
     *
     * @var Booking
     */
    public $booking;

    /**
     * The booking instance.
     *
     * @var Booking
     */
    public $changes;

    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @param $changes
     */
    public function __construct(Booking $booking, Collection $changes)
    {
        $this->booking = $booking;
        $this->changes = $changes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.booking.changed');
    }
}
