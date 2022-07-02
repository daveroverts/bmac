<?php

namespace Tests\Unit;

use App\Models\Event;
use Tests\TestCase;

it('creates new event', function () {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => $event->name,
        'slug' => $event->slug,
        'description' => $event->description,
        'dep' => $event->dep,
        'arr' => $event->arr,
        'startEvent' => $event->startEvent,
        'endEvent' => $event->endEvent,
        'startBooking' => $event->startBooking,
        'endBooking' => $event->endBooking,
    ]);
});
