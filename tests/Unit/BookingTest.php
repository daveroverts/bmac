<?php

namespace Tests\Unit;

use App\Models\Booking;
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
        $booking = factory(\App\Models\Booking::class)->make();

        Booking::create($booking->toArray());

        $this->assertDatabaseHas('bookings', $booking->toArray());
    }
}
