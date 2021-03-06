<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Collection;

class BookingChanged extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * The booking instance.
     *
     * @var Booking
     */
    public $booking;

    /**
     * All changes
     *
     * @var Booking
     */
    public $changes;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Booking $booking, Collection $changes)
    {
        $this->booking = $booking;
        $this->changes = $changes;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $booking = $this->booking;
        $changes = $this->changes;

        return (new MailMessage)->markdown('emails.booking.changed', ['booking' => $booking, 'changes' => $changes]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
