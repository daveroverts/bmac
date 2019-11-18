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
     * The event instance.
     *
     * @var Event
     */
    public $event;

    /**
     * The subject.
     *
     */
    public $subject;

    /**
     * The message.
     *
     */
    public $content;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct($event, $subject, $content)
    {
        $this->event = $event;
        $this->subject = $subject;
        $this->content = $content;
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
        $subject = $this->event->name.': '.$this->subject;
        $content = $this->content;
        return (new MailMessage)
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
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $notifiable->id,
            'subject' => $subject = $this->event->name.': '.$this->subject,
            'content' => $this->content,
        ];
    }
}
