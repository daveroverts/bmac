<?php

namespace App\Mail;

use App\Models\{Event, User};
use Illuminate\{Bus\Queueable, Contracts\Queue\ShouldQueue, Mail\Mailable, Queue\SerializesModels};

class BookingDeleted extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * The event instance.
     *
     * @var Event
     */
    public $event;

    /**
     * The user instance.
     *
     * @var User
     */
    public $user;

    /**
     * Create a new message instance.
     *
     * @param Event $event
     * @param User $user
     */
    public function __construct(Event $event, User $user)
    {
        $this->event = $event;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.booking.deleted');
    }
}
