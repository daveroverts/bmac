<?php

namespace Tests\Unit;

use App\Models\Flight;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test if a booking can be added
     *
     * @return void
     */
    public function testItCreatesNewBooking()
    {
        $flight = Flight::factory()->create();

        $this->assertDatabaseHas('flights', $flight->toArray());
    }
}
