<?php

namespace Tests\Unit;

use App\Models\Flight;
use Tests\TestCase;

it('can creates new booking', function () {
    /** @var TestCase $this */

    /** @var Flight $flight */
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
});
