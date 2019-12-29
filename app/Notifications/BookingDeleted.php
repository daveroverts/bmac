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
     * The event instance.
     *
     * @var Event
     */
    public $event;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Event $event)
    {
        $this->event = $event;
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

        return (new MailMessage)
            ->greeting('Booking deleted')
            ->line('Dear '.$notifiable->full_name.',')
            ->line('Your booking for '.$event->name.' event has been removed by an administrator. If you would like to know why, please send a E-mail to [events@dutchvacc.nl](mailto:events@dutchvacc.nl)')
            ->line('As long as the bookings remain open ('.$event->endBooking->format('d-m-Y H:i').'z), you can still create a new booking.')
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
