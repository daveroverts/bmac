<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tests\TestCase;

it('can creates new booking', function (): void {
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

it('has BelongsTo relationships for event and user', function (): void {
    $booking = Booking::factory()->create();

    expect($booking->event())->toBeInstanceOf(BelongsTo::class);
    expect($booking->user())->toBeInstanceOf(BelongsTo::class);
});

it('has BelongsTo relationships for airportDep and airportArr', function (): void {
    $booking = Booking::factory()->create();

    expect($booking->airportDep())->toBeInstanceOf(BelongsTo::class);
    expect($booking->airportArr())->toBeInstanceOf(BelongsTo::class);
});

it('filters booked bookings using scope', function (): void {
    Booking::factory()->booked()->create();
    Booking::factory()->unassigned()->create();
    Booking::factory()->reserved()->create();

    $results = Booking::query()->booked()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->status)->toBe(\App\Enums\BookingStatus::BOOKED);
});

it('filters unassigned bookings using scope', function (): void {
    Booking::factory()->booked()->create();
    Booking::factory()->unassigned()->create();
    Booking::factory()->reserved()->create();

    $results = Booking::query()->unassigned()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->status)->toBe(\App\Enums\BookingStatus::UNASSIGNED);
});

it('filters reserved bookings using scope', function (): void {
    Booking::factory()->booked()->create();
    Booking::factory()->unassigned()->create();
    Booking::factory()->reserved()->create();

    $results = Booking::query()->reserved()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->status)->toBe(\App\Enums\BookingStatus::RESERVED);
});
