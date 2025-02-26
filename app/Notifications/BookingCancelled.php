<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BookingCancelled extends Notification implements ShouldQueue
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
     */
    public function via(mixed $notifiable): array
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
        $eventName = $this->event->name;
        $subject = $eventName . ': ' . __('Booking cancelled');
        return (new MailMessage())
            ->subject($subject)
            ->greeting('Booking cancelled')
            ->line('Dear ' . $notifiable->full_name . ',')
            ->line(sprintf("We've processed your cancellation for the %s event and opened the slot you held for other pilots to book. Thanks for letting us know.", $eventName))
            ->line('We hope to see you again soon.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(mixed $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
        ];
    }
}
