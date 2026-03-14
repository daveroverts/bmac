<?php

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Flight;
use App\Models\User;

// ──────────────────────────────────────────────────────────────
// Booking accessors
// ──────────────────────────────────────────────────────────────

it('formatted_callsign returns the callsign when set', function (): void {
    $booking = new Booking();
    $booking->callsign = 'BAW123';

    expect($booking->formatted_callsign)->toBe('BAW123');
});

it('formatted_callsign returns dash when callsign is null', function (): void {
    $booking = new Booking();

    expect($booking->formatted_callsign)->toBe('-');
});

it('formatted_actype returns the acType when set', function (): void {
    $booking = new Booking();
    $booking->acType = 'B738';

    expect($booking->formatted_actype)->toBe('B738');
});

it('formatted_actype returns dash when acType is null', function (): void {
    $booking = new Booking();

    expect($booking->formatted_actype)->toBe('-');
});

it('formatted_selcal returns the selcal when set', function (): void {
    $booking = Booking::factory()->create();
    $booking->selcal = 'ABCD';
    $booking->save();

    expect($booking->formatted_selcal)->toBe('ABCD');
});

it('formatted_selcal returns dash when selcal is null', function (): void {
    $booking = Booking::factory()->create();

    expect($booking->formatted_selcal)->toBe('-');
});

it('has_received_final_information_email returns true when email was sent', function (): void {
    $booking = new Booking();
    $booking->forceFill(['final_information_email_sent_at' => now()]);

    expect($booking->has_received_final_information_email)->toBeTrue();
});

it('has_received_final_information_email returns false when email was not sent', function (): void {
    $booking = new Booking();

    expect($booking->has_received_final_information_email)->toBeFalse();
});

// ──────────────────────────────────────────────────────────────
// Booking mutators
// ──────────────────────────────────────────────────────────────

it('callsign mutator stores value as uppercase', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $booking->callsign = 'baw123';
    $booking->save();

    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'callsign' => 'BAW123']);
});

it('callsign mutator stores null when value is empty', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create(['callsign' => 'BAW123']);
    $booking->callsign = '';
    $booking->save();

    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'callsign' => null]);
});

it('actype mutator stores value as uppercase', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $booking->acType = 'b738';
    $booking->save();

    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'acType' => 'B738']);
});

it('actype mutator stores null when value is empty', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create(['acType' => 'B738']);
    $booking->acType = '';
    $booking->save();

    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'acType' => null]);
});

it('selcal mutator stores value as uppercase', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $booking->selcal = 'abcd';
    $booking->save();

    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'selcal' => 'ABCD']);
});

it('selcal mutator stores null when value is empty', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $booking->selcal = 'ABCD';
    $booking->save();
    $booking->selcal = '';
    $booking->save();

    $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'selcal' => null]);
});

// ──────────────────────────────────────────────────────────────
// Flight accessors
// ──────────────────────────────────────────────────────────────

it('formatted_ctot returns formatted time string when ctot is set', function (): void {
    $flight = new Flight();
    $flight->forceFill(['ctot' => now()->setTime(14, 30)]);

    expect($flight->formatted_ctot)->toBe('1430z');
});

it('formatted_ctot returns dash when ctot is null', function (): void {
    $flight = new Flight();

    expect($flight->formatted_ctot)->toBe('-');
});

it('formatted_eta returns formatted time string when eta is set', function (): void {
    $flight = new Flight();
    $flight->forceFill(['eta' => now()->setTime(16, 45)]);

    expect($flight->formatted_eta)->toBe('1645z');
});

it('formatted_eta returns dash when eta is null', function (): void {
    $flight = new Flight();

    expect($flight->formatted_eta)->toBe('-');
});

it('formatted_oceanicfl returns FL prefix with value when oceanicFL is set', function (): void {
    $flight = new Flight(['oceanicFL' => '350']);

    expect($flight->formatted_oceanicfl)->toBe('FL350');
});

it('formatted_oceanicfl returns dash when oceanicFL is null', function (): void {
    $flight = new Flight();

    expect($flight->formatted_oceanicfl)->toBe('-');
});

it('formatted_notes returns the notes when set', function (): void {
    $flight = new Flight(['notes' => 'Speed 400kts']);

    expect($flight->formatted_notes)->toBe('Speed 400kts');
});

it('formatted_notes returns dash when notes is null', function (): void {
    $flight = new Flight();

    expect($flight->formatted_notes)->toBe('-');
});

// ──────────────────────────────────────────────────────────────
// Flight mutators
// ──────────────────────────────────────────────────────────────

it('route mutator stores value as uppercase', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $flight = $booking->flights()->create(['route' => 'dct helen']);

    $this->assertDatabaseHas('flights', ['id' => $flight->id, 'route' => 'DCT HELEN']);
});

it('route mutator stores null when value is empty', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $flight = $booking->flights()->create(['route' => null]);

    $this->assertDatabaseHas('flights', ['id' => $flight->id, 'route' => null]);
});

it('oceanictrack mutator stores value as uppercase', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $flight = $booking->flights()->create(['oceanicTrack' => 'track a']);

    $this->assertDatabaseHas('flights', ['id' => $flight->id, 'oceanicTrack' => 'TRACK A']);
});

it('oceanictrack mutator stores null when value is empty', function (): void {
    /** @var Tests\TestCase $this */
    $booking = Booking::factory()->create();
    $flight = $booking->flights()->create(['oceanicTrack' => null]);

    $this->assertDatabaseHas('flights', ['id' => $flight->id, 'oceanicTrack' => null]);
});

// ──────────────────────────────────────────────────────────────
// Airport accessors
// ──────────────────────────────────────────────────────────────

it('full_name returns dash when airport has no ID', function (): void {
    $airport = new Airport();

    expect($airport->full_name)->toBe('-');
});

it('full_name returns NAME-view abbr when airport_view is NAME', function (): void {
    // TestCase logs in as admin whose airport_view defaults to 0 (NAME)
    $airport = Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam']);

    expect($airport->full_name)->toBe('<abbr title="EHAM | [AMS]">Amsterdam</abbr>');
});

it('full_name returns ICAO-view abbr when airport_view is ICAO', function (): void {
    /** @var Tests\TestCase $this */
    /** @var \App\Models\User $user */
    $user = User::factory()->airportViewIcao()->create();
    $this->actingAs($user);
    $airport = Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam']);

    expect($airport->full_name)->toBe('<abbr title="Amsterdam | [AMS]">EHAM</abbr>');
});

it('full_name returns IATA-view abbr when airport_view is IATA', function (): void {
    /** @var Tests\TestCase $this */
    /** @var \App\Models\User $user */
    $user = User::factory()->airportViewIata()->create();
    $this->actingAs($user);
    $airport = Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam']);

    expect($airport->full_name)->toBe('<abbr title="Amsterdam | [EHAM]">AMS</abbr>');
});

// ──────────────────────────────────────────────────────────────
// Airport mutators
// ──────────────────────────────────────────────────────────────

it('icao mutator stores value as uppercase', function (): void {
    /** @var Tests\TestCase $this */
    $airport = Airport::factory()->create(['icao' => 'eham']);

    $this->assertDatabaseHas('airports', ['id' => $airport->id, 'icao' => 'EHAM']);
});

it('iata mutator stores value as uppercase', function (): void {
    /** @var Tests\TestCase $this */
    $airport = Airport::factory()->create(['iata' => 'ams']);

    $this->assertDatabaseHas('airports', ['id' => $airport->id, 'iata' => 'AMS']);
});

// ──────────────────────────────────────────────────────────────
// User accessors
// ──────────────────────────────────────────────────────────────

it('full_name returns ucfirst first and last name', function (): void {
    $user = User::factory()->create(['name_first' => 'john', 'name_last' => 'doe']);

    expect($user->full_name)->toBe('John Doe');
});

it('pic returns formatted name and ID when user has both', function (): void {
    $user = User::factory()->create(['name_first' => 'john', 'name_last' => 'doe']);

    expect($user->pic)->toBe('John Doe | ' . $user->id);
});
