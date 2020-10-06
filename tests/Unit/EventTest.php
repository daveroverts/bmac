<?php

namespace Tests\Unit;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventTest extends TestCase
{

    use RefreshDatabase;

    /**
     * Test if a event can be added
     *
     * @return void
     */
    public function testItCreatesNewEvent()
    {
        $event = Event::factory()->create();

        $this->assertDatabaseHas('events', $event->toArray());
    }
}
