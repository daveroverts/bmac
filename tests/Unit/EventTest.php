<?php

namespace Tests\Unit;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if a event can be added.
     *
     * @return void
     */
    public function testItCreatesNewEvent()
    {
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
    }
}
