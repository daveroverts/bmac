<?php

namespace Tests\Unit;

use App\Models\EventLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventLinkTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesNewEventLink()
    {
        $eventLink = EventLink::factory()->create();

        $this->assertDatabaseHas('event_links', [
            'id' => $eventLink->id,
            'event_id' => $eventLink->event_id,
            'event_link_type_id' => $eventLink->event_link_type_id,
            'url' => $eventLink->url,
        ]);
    }
}
