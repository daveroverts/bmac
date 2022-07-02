<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Faq;
use Tests\TestCase;

it('creates new FAQ', function () {
    /** @var TestCase $this */

    /** @var Faq $faq */
    $faq = Faq::factory()->create();

    $this->assertDatabaseHas('faqs', [
        'id' => $faq->id,
        'question' => $faq->question,
        'answer' => $faq->answer,
    ]);
});

it('links faq to event', function () {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()
        ->has(Faq::factory())
        ->create();

    $this->assertDatabaseHas('event_faq', [
        'event_id' => $event->id,
        'faq_id' => $event->faqs()->first()->id
    ]);
});
