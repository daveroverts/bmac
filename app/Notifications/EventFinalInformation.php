<?php

namespace App\Notifications;

use App\Enums\EventType;
use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EventFinalInformation extends Notification implements ShouldQueue
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
        $subject = $booking->event->name . ': ' . __('Booking confirmed');
        $template = $booking->event->event_type_id == EventType::MULTIFLIGHTS->value ? 'emails.event.finalInformation_multiflights' : 'emails.event.finalInformation';
        $flight = $booking->flights->first() ?? null;
        return (new MailMessage())
            ->subject($subject)
            ->markdown($template, compact('booking', 'flight'));
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
            $this->booking
        ];
    }
}
