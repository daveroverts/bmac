<?php

namespace App\Mail;

use Illuminate\{Bus\Queueable, Contracts\Queue\ShouldQueue, Mail\Mailable, Queue\SerializesModels};

class EventNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.event.notification');
    }
}
