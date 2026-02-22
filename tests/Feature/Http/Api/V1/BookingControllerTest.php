<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use Tests\TestCase;

it('returns a single booking by uuid', function (): void {
    /** @var TestCase $this */

    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create()->id,
    ]);

    $this->getJson('/api/v1/bookings/' . $flight->booking->uuid)
        ->assertOk()
        ->assertJsonPath('data.uuid', $flight->booking->uuid)
        ->assertJsonPath('data.links.dep', route('v1.airports.show', $flight->airportDep))
        ->assertJsonPath('data.links.arr', route('v1.airports.show', $flight->airportArr));
});

it('returns 404 for a non-existent booking', function (): void {
    /** @var TestCase $this */

    $this->getJson('/api/v1/bookings/non-existent-uuid')
        ->assertNotFound();
});

it('returns only booked bookings for an event', function (): void {
    /** @var TestCase $this */

    $event = Event::factory()->create();

    $bookedBooking = Booking::factory()->booked()->create(['event_id' => $event->id]);
    Flight::factory()->create(['booking_id' => $bookedBooking->id, 'dep' => $bookedBooking->event->dep, 'arr' => $bookedBooking->event->arr]);

    $reservedBooking = Booking::factory()->reserved()->create(['event_id' => $event->id]);
    Flight::factory()->create(['booking_id' => $reservedBooking->id, 'dep' => $reservedBooking->event->dep, 'arr' => $reservedBooking->event->arr]);

    $unassignedBooking = Booking::factory()->unassigned()->create(['event_id' => $event->id]);
    Flight::factory()->create(['booking_id' => $unassignedBooking->id, 'dep' => $unassignedBooking->event->dep, 'arr' => $unassignedBooking->event->arr]);

    $response = $this->getJson(sprintf('/api/v1/events/%s/bookings', $event->slug))
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.uuid'))->toBe($bookedBooking->uuid);
});

it('returns empty data when an event has no booked bookings', function (): void {
    /** @var TestCase $this */

    $event = Event::factory()->create();
    Booking::factory()->unassigned()->create(['event_id' => $event->id]);

    $response = $this->getJson(sprintf('/api/v1/events/%s/bookings', $event->slug))
        ->assertOk();

    expect($response->json('data'))->toHaveCount(0);
});

it('does not include deprecation headers on v1 booking routes', function (): void {
    /** @var TestCase $this */

    $event = Event::factory()->create();

    $this->getJson(sprintf('/api/v1/events/%s/bookings', $event->slug))
        ->assertOk()
        ->assertHeaderMissing('Deprecation')
        ->assertHeaderMissing('Sunset');
});
