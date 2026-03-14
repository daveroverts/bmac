<?php

use App\Models\Airport;
use App\Models\Booking;

it('returns HTML abbr output from airportCtot when flight exists', function (): void {
    $dep = Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam Schiphol']);
    $arr = Airport::factory()->create(['icao' => 'EGLL', 'iata' => 'LHR', 'name' => 'London Heathrow']);

    $booking = Booking::factory()->create();
    $booking->flights()->create([
        'order_by' => 1,
        'dep' => $dep->id,
        'arr' => $arr->id,
        'ctot' => now()->setTime(14, 30),
    ]);

    $booking->load('flights.airportDep', 'flights.airportArr');

    $result = $booking->airportCtot(1);

    expect($result)
        ->toContain('<abbr')
        ->toContain('EHAM')
        ->toContain('EGLL')
        ->toContain('AMS')
        ->toContain('LHR')
        ->toContain('Amsterdam Schiphol')
        ->toContain('London Heathrow')
        ->toContain('1430z');
});

it('returns plain text from airportCtot when withAbbr is false', function (): void {
    $dep = Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam Schiphol']);
    $arr = Airport::factory()->create(['icao' => 'EGLL', 'iata' => 'LHR', 'name' => 'London Heathrow']);

    $booking = Booking::factory()->create();
    $booking->flights()->create([
        'order_by' => 1,
        'dep' => $dep->id,
        'arr' => $arr->id,
        'ctot' => now()->setTime(14, 30),
    ]);

    $booking->load('flights.airportDep', 'flights.airportArr');

    $result = $booking->airportCtot(1, false);

    expect($result)
        ->not->toContain('<abbr')
        ->toContain('EHAM')
        ->toContain('EGLL')
        ->toContain('1430z');
});

it('returns dash from airportCtot when no flight matches order_by', function (): void {
    $booking = Booking::factory()->create();
    $booking->flights()->create([
        'order_by' => 1,
        'dep' => $booking->event->dep,
        'arr' => $booking->event->arr,
    ]);

    $booking->load('flights.airportDep', 'flights.airportArr');

    expect($booking->airportCtot(99))->toBe('-');
});

it('returns dash from airportCtot when booking has no flights', function (): void {
    $booking = Booking::factory()->create();
    $booking->setRelation('flights', collect());

    expect($booking->airportCtot(1))->toBe('-');
});

it('returns unique airports from uniqueAirports', function (): void {
    $airportA = Airport::factory()->create();
    $airportB = Airport::factory()->create();
    $airportC = Airport::factory()->create();

    $booking = Booking::factory()->create();
    $booking->flights()->create([
        'order_by' => 1,
        'dep' => $airportA->id,
        'arr' => $airportB->id,
    ]);
    $booking->flights()->create([
        'order_by' => 2,
        'dep' => $airportB->id,
        'arr' => $airportC->id,
    ]);

    $uniqueAirports = $booking->uniqueAirports();

    expect($uniqueAirports)->toHaveCount(3)
        ->and($uniqueAirports->pluck('id')->sort()->values()->all())
        ->toBe(collect([$airportA->id, $airportB->id, $airportC->id])->sort()->values()->all());
});

it('returns empty collection from uniqueAirports when booking has no flights', function (): void {
    $booking = Booking::factory()->create();

    expect($booking->uniqueAirports())->toHaveCount(0);
});
