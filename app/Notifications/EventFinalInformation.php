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
     * The booking instance.
     *
     * @var Booking
     */
    public $booking;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
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
        $flight1 = $booking->flights()->first();
        $flight2 = $booking->flights()->whereKeyNot($flight1->id)->first();
        $template = $booking->event->event_type_id == EventType::MULTIFLIGHTS ? 'emails.event.finalInformation_multiflights' : 'emails.event.finalInformation';

        return (new MailMessage)->markdown($template, compact('booking', 'flight1', 'flight2'));
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
