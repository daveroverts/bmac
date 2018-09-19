<?php

namespace Tests\Unit;

use App\Models\Event;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class EventTest extends TestCase
{

    /**
     * Test if a event can be added
     *
     * @return void
     */
    public function testItCreatesNewEvent()
    {
        $name = 'Amsterdam - Eelde Fly-In';
        $description = 'Si, Fly';
        $startEvent = now()->addMonth()->toDateTimeString();
        $endEvent = now()->addMonth()->addHours(3)->toDateTimeString();
        $startBooking = now()->addWeek()->toDateTimeString();
        $endBooking = now()->subDay()->toDateTimeString();
        $sendFeedbackForm = now()->addMonth()->addHours(2)->addDay()->toDateTimeString();

        Event::create([
            'name' => $name,
            'description' => $description,
            'startEvent' => $startEvent,
            'endEvent' => $endEvent,
            'startBooking' => $startBooking,
            'endBooking' => $endBooking,
            'sendFeedbackForm' => $sendFeedbackForm,
        ]);

        $this->assertDatabaseHas('events', [
            'name' => $name,
            'description' => $description,
            'startEvent' => $startEvent,
            'endEvent' => $endEvent,
            'startBooking' => $startBooking,
            'endBooking' => $endBooking,
            'sendFeedbackForm' => $sendFeedbackForm,
        ]);
    }
}
