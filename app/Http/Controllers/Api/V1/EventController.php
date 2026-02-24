<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EventResource;
use App\Http\Resources\EventsCollection;
use App\Models\Event;

class EventController extends Controller
{
    /**
     * Return a paginated list of all events.
     */
    public function index(): EventsCollection
    {
        return new EventsCollection(Event::query()->paginate());
    }

    /**
     * Return a single event.
     */
    public function show(Event $event): EventResource
    {
        return new EventResource($event);
    }

    /**
     * Return upcoming online events, optionally limited.
     */
    public function upcoming(int $limit = 3): EventsCollection
    {
        $limit = min(max(1, $limit), 50);

        $events = Event::query()
            ->where('is_online', true)
            ->where('endEvent', '>', now())
            ->orderBy('startEvent', 'asc')
            ->limit($limit)
            ->get();

        return new EventsCollection($events);
    }
}
