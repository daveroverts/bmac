<?php

namespace Tests\Unit;

use App\Models\Airport;
use App\Models\Event;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    private $airport;
    private $event;

    public function setUp()
    {
        parent::setUp();

        $this->airport = factory(Airport::class)->create([
            'icao' => 'EHGG',
            'iata' => 'GRQ',
            'name' => 'Groningen Airport Eelde',
        ]);

        $this->event = factory(Event::class)->create([
            'name' => 'Amsterdam - Eelde Fly-In',
            'description' => 'Si, Fly',
            'startEvent' => now()->addMonth()->toDateTimeString(),
            'endEvent' => now()->addMonth()->addHours(3)->toDateTimeString(),
            'startBooking' => now()->addWeek()->toDateTimeString(),
            'endBooking' => now()->subDay()->toDateTimeString(),
            'sendFeedbackForm' => now()->addMonth()->addHours(2)->addDay()->toDateTimeString(),
        ]);
    }

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->assertTrue(true);
    }
}
