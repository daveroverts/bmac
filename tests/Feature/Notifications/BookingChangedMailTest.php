<?php

use App\Models\Airport;
use App\Models\Booking;
use App\Notifications\BookingChanged;

function renderBookingChangedMail(Booking $booking, array $changes): string
{
    return (string) (new BookingChanged($booking, collect($changes)))
        ->toMail($booking->user)
        ->render();
}

it('names the previous airport instead of the first airport in the table', function (): void {
    $booking = Booking::factory()->booked()->create();
    $oldAirport = Airport::factory()->create();
    $newAirport = Airport::factory()->create();

    $html = renderBookingChangedMail($booking, [
        ['name' => 'dep', 'old' => $oldAirport->id, 'new' => $newAirport->id],
    ]);

    expect($html)->toContain($newAirport->icao)
        ->and($html)->toContain("(was {$oldAirport->icao})");
});

it('shows a dash when the previous airport is empty', function (): void {
    $booking = Booking::factory()->booked()->create();
    $newAirport = Airport::factory()->create();

    $html = renderBookingChangedMail($booking, [
        ['name' => 'arr', 'old' => null, 'new' => $newAirport->id],
    ]);

    expect($html)->toContain($newAirport->icao)
        ->and($html)->toContain('(was -)');
});

it('shows a dash instead of the current time when a CTOT is set for the first time', function (): void {
    $booking = Booking::factory()->booked()->create();

    $html = renderBookingChangedMail($booking, [
        ['name' => 'ctot', 'old' => null, 'new' => '2029-10-07 19:30:00'],
        ['name' => 'eta', 'old' => '2029-10-07 20:30:00', 'new' => null],
    ]);

    expect($html)->toContain('1930z')
        ->and($html)->toContain('(was -)')
        ->and($html)->toContain('(was 2030z)');
});
