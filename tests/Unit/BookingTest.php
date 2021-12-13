<?php

namespace Tests\Unit;

use App\Models\Flight;
use Tests\TestCase;
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

        $this->assertDatabaseHas('flights', [
            'id' => $flight->id,
            'booking_id' => $flight->booking_id,
            'dep' => $flight->dep,
            'arr' => $flight->arr,
        ]);

        $this->assertDatabaseHas('bookings', [
            'id' => $flight->booking->id,
            'event_id' => $flight->booking->event_id,
        ]);
    }
}
