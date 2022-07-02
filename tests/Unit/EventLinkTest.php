<?php

namespace Tests\Unit;

use App\Models\EventLink;
use Tests\TestCase;

it('creates new event link', function () {
    /** @var TestCase $this */

    /** @var EventLink $eventLink */
    $eventLink = EventLink::factory()->create();

    $this->assertDatabaseHas('event_links', [
        'id' => $eventLink->id,
        'event_id' => $eventLink->event_id,
        'event_link_type_id' => $eventLink->event_link_type_id,
        'url' => $eventLink->url,
    ]);
});
