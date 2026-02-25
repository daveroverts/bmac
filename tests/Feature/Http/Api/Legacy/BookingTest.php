<?php

use App\Models\Booking;
use App\Models\Flight;
use Tests\TestCase;

it('includes full_name in legacy booking response', function (): void {
    /** @var TestCase $this */

    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create()->id,
    ]);

    $this->getJson('/api/bookings/' . $flight->booking->uuid)
        ->assertOk()
        ->assertJsonPath('data.full_name', $flight->booking->user->full_name);
});

it('includes deprecation headers on legacy booking routes', function (): void {
    /** @var TestCase $this */

    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create()->id,
    ]);

    $this->getJson('/api/bookings/' . $flight->booking->uuid)
        ->assertOk()
        ->assertHeader('Deprecation', 'true')
        ->assertHeader('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT');
});
