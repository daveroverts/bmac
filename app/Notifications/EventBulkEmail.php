<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EventBulkEmail extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Event $event, public string $subject, public string $content)
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
        $subject = $this->event->name . ': ' . $this->subject;
        $content = $this->content;
        return (new MailMessage())
            ->subject($subject)
            ->markdown('emails.event.bulkEmail', [
                'full_name' => $notifiable->full_name,
                'subject' => $subject,
                'content' => $content
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array
     */
    public function toArray(mixed $notifiable)
    {
        return [
            'user_id' => $notifiable->id,
            'subject' => $this->event->name . ': ' . $this->subject,
            'content' => $this->content,
        ];
    }
}
