<?php

use Tests\TestCase;
use App\Models\Event;

it('can render event page', function () {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->get(route('events.show', $event))
        ->assertOk()
        ->assertSee($event->name);

    $this->get(route('events.show', 'some-random-string'))
        ->assertNotFound();
});
