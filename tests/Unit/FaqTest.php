<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Faq;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FaqTest extends TestCase
{

    use RefreshDatabase;
    /**
     * Test if a FAQ can be added
     *
     * @return void
     */
    public function testItCreatesNewFaq()
    {
        $faq = Faq::factory()->create();

        $this->assertDatabaseHas('faqs', $faq->toArray());
    }

    public function testItLinksFaqToEvent()
    {
        $event = Event::factory()
        ->has(Faq::factory())
        ->create();

        $this->assertDatabaseHas('event_faq', [
            'event_id' => $event->id,
            'faq_id' => $event->faqs->first()->id
        ]);
    }
}
