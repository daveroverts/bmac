<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Faq;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FaqTest extends TestCase
{
    use RefreshDatabase;

    public function testItCreatesNewFaq()
    {
        $faq = Faq::factory()->create();

        $this->assertDatabaseHas('faqs', [
            'id' => $faq->id,
            'question' => $faq->question,
            'answer' => $faq->answer,
        ]);
    }

    public function testItLinksFaqToEvent()
    {
        $event = Event::factory()
            ->has(Faq::factory())
            ->create();

        $this->assertDatabaseHas('event_faq', [
            'event_id' => $event->id,
            'faq_id' => $event->faqs()->first()->id
        ]);
    }
}
