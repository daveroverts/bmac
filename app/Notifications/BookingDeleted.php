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
     * @param  array<int, string>  $callsigns
     */
    public function __construct(public Event $event, public array $callsigns = [])
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
        $event = $this->event;
        $subject = $event->name . ': ' . __('Booking deleted');

        return (new MailMessage())
            ->subject($subject)
            ->greeting('Booking deleted')
            ->line('Dear ' . $notifiable->full_name . ',')
            ->line($this->removalLine())
            ->line('As long as the bookings remain open (' . $event->endBooking->format('d-m-Y H:i') . 'z), you can still create a new booking.')
            ->action('Book new flight', route('events.bookings.index', $event));
    }

    private function removalLine(): string
    {
        if (count($this->callsigns) > 1) {
            $named = array_diff($this->callsigns, ['-']);
            $list = $named === [] ? '' : ' (' . implode(', ', $named) . ')';

            return 'Your ' . count($this->callsigns) . ' bookings for ' . $this->event->name . ' event' . $list . ' have been removed by an administrator.';
        }

        return 'Your booking for ' . $this->event->name . ' event has been removed by an administrator.';
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
