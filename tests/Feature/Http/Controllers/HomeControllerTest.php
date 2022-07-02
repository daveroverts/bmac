<?php

use Tests\TestCase;
use App\Models\Event;

it('can render homepage', function () {
    /** @var TestCase $this */
    $this->get('/')->assertOk();
});


it('can render events', function () {
    /** @var TestCase $this */

    /** @var Event $event1 */
    $event1 = Event::factory()->expired()->create();

    /** @var Event $event2 */
    $event2 = Event::factory()->create();

    /** @var Event $event3 */
    $event3 = Event::factory()->notOnHomePage()->create();

    $this->get('/')
        ->assertOk()
        ->assertDontSee($event1->name)
        ->assertSee($event2->name)
        ->assertDontSee($event3->name);
});
