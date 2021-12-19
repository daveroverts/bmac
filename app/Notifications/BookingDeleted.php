<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BookingDeleted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Event $event)
    {
        //
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
        $event = $this->event;
        $subject = $event->name . ': ' . __('Booking deleted');

        return (new MailMessage())
            ->subject($subject)
            ->greeting('Booking deleted')
            ->line('Dear ' . $notifiable->full_name . ',')
            ->line('Your booking for ' . $event->name . ' event has been removed by an administrator.')
            ->line('As long as the bookings remain open (' . $event->endBooking->format('d-m-Y H:i') . 'z), you can still create a new booking.')
            ->action('Book new flight', route('bookings.event.index', $event));
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
            'event_id' => $this->event->id,
        ];
    }
}
