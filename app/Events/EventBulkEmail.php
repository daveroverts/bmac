<?php

namespace App\Events;

use App\Models\Event;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EventBulkEmail
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $event;
    public $request;
    /* @var \App\Models\User|null */
    public $users;

    /**
     * Create a new event instance.
     *
     * @param Event $event
     * @param array $request
     * @param \App\Models\User|null $users
     *
     * @return void
     */
    public function __construct(Event $event, array $request, $users)
    {
        $this->event = $event;
        $this->request = $request;
        $this->users = $users;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
