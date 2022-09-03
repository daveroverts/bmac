<?php

use App\Models\Faq;
use Tests\TestCase;
use App\Models\Event;

it('can render faq page with no items', function () {
    /** @var TestCase $this */
    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('General FAQ')
        ->assertSee('No Questions / Answers are available at the moment');
});

it('can render faq page with generic items', function () {
    /** @var TestCase $this */

    /** @var Faq $faq1 */
    $faq1 = Faq::factory()->create();

    /** @var Faq $faq2 */
    $faq2 = Faq::factory()->offline()->create();

    $this->get(route('faq'))
        ->assertOk()
        ->assertSee('General FAQ')
        ->assertDontSee('No Questions / Answers are available at the moment')
        ->assertSee($faq1->question)
        ->assertSee($faq1->answer)
        ->assertDontSee($faq2->question)
        ->assertDontSee($faq2->answer);
});

it('can render faq page with event items', function () {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->hasAttached(Faq::factory())->create();

    $this->get(route('faq'))
        ->assertOk()
        ->assertSee("FAQ for {$event->name}")
        ->assertSee($event->faqs->first()->question)
        ->assertSee($event->faqs->first()->answer)
        ->assertSee('General FAQ')
        ->assertSee('No Questions / Answers are available at the moment');
});
