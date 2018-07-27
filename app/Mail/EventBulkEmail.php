<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventBulkEmail extends Mailable implements ShouldQueue
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
     * The user instance.
     *
     * @var User
     */
    public $email;


    /**
     * Create a new message instance.
     *
     * @param Event $event
     * @param User $user
     * @param $email
     */
    public function __construct(Event $event, User $user, $email)
    {
        $this->event = $event;
        $this->user = $user;
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->markdown('emails.event.bulkEmail');
    }
}
