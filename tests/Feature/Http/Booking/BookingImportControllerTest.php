<?php

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

it('imports bookings from a CSV file', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $depAirport = Airport::factory()->create(['icao' => 'EHAM']);
    $arrAirport = Airport::factory()->create(['icao' => 'EGLL']);

    /** @var Event $event */
    $event = Event::factory()->create([
        'dep' => $depAirport->id,
        'arr' => $arrAirport->id,
    ]);

    $csvContent = "origin,destination,call_sign,aircraft_type,notes\n";
    $csvContent .= "EHAM,EGLL,KLM01,B738,Test note\n";
    $csvContent .= "EHAM,EGLL,,,,\n";

    $file = UploadedFile::fake()->createWithContent('bookings.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.import.store', $event), [
            'file' => $file,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    expect(Booking::where('event_id', $event->id)->count())->toBe(2);

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => 'KLM01',
        'acType' => 'B738',
        'is_editable' => false,
    ]);

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => null,
        'is_editable' => true,
    ]);
});

it('imports flight times from a CSV file', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    Airport::factory()->create(['icao' => 'EHAM']);
    Airport::factory()->create(['icao' => 'EGLL']);

    /** @var Event $event */
    $event = Event::factory()->create([
        'startEvent' => '2026-07-15 00:00:00',
    ]);

    $csvContent = "origin,destination,call_sign,aircraft_type,ctot,eta\n";
    $csvContent .= "EHAM,EGLL,KLM01,B738,14:30,15:45\n";

    $file = UploadedFile::fake()->createWithContent('bookings.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.import.store', $event), [
            'file' => $file,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    $flight = Booking::where('event_id', $event->id)->firstOrFail()->flights()->firstOrFail();

    expect($flight->ctot->format('Y-m-d H:i'))->toBe('2026-07-15 14:30')
        ->and($flight->eta->format('Y-m-d H:i'))->toBe('2026-07-15 15:45');
});

it('rejects import with a disallowed file type', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->create('bookings.txt', 100, 'text/plain');

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.import.store', $event), [
            'file' => $file,
        ])
        ->assertSessionHasErrors('file');
});

it('rejects import when file exceeds max size', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->create('bookings.csv', 11000, 'text/csv');

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.import.store', $event), [
            'file' => $file,
        ])
        ->assertSessionHasErrors('file');
});
