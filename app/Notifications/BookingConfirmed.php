<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BookingConfirmed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Booking $booking)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array
     */
    public function via(mixed $notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @return MailMessage
     */
    public function toMail(mixed $notifiable)
    {
        $booking = $this->booking;
        $subject = $booking->event->name . ': ' . __('Booking confirmed');

        return (new MailMessage())
            ->subject($subject)
            ->markdown('emails.booking.confirmed', ['booking' => $booking]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(mixed $notifiable)
    {
        return [
            //
        ];
    }
}
