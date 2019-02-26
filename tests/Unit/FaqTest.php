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
        $faq = factory(Faq::class)->make();

        Faq::create($faq->toArray());

        $this->assertDatabaseHas('faqs', $faq->toArray());
    }

    public function testItLinksFaqToEvent()
    {
        $faq = factory(Faq::class)->make();
        $faqModel = Faq::create($faq->toArray());

        $event = factory(Event::class)->make();
        $eventModel = Event::create($event->toArray());

        $faqModel->events()->attach($eventModel->id);

        $this->assertDatabaseHas('event_faq', [
            'event_id' => $eventModel->id,
            'faq_id' => $faqModel->id
        ]);
    }
}
