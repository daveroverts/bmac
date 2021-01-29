<?php

namespace Tests\Unit;

use App\Models\EventLink;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventLinkTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesNewEventLink()
    {
        $eventLink = EventLink::factory()->create();

        $this->assertDatabaseHas('event_links', $eventLink->toArray());
    }
}
