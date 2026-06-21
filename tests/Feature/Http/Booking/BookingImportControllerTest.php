<?php

use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Services\AirportImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
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
    $csvContent .= "EHAM,EGLL,KLM02,,Test note\n";
    $csvContent .= "EHAM,EGLL,,,,\n";

    $file = UploadedFile::fake()->createWithContent('bookings.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.import.store', $event), [
            'file' => $file,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    expect(Booking::where('event_id', $event->id)->count())->toBe(3);

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => 'KLM01',
        'acType' => 'B738',
        'is_callsign_editable' => false,
        'is_actype_editable' => false,
    ]);

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => 'KLM02',
        'acType' => null,
        'is_callsign_editable' => false,
        'is_actype_editable' => true,
    ]);

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => null,
        'is_callsign_editable' => true,
        'is_actype_editable' => true,
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

it('auto-creates missing airports during import', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $depAirport = Airport::factory()->create(['icao' => 'EHAM']);

    /** @var Event $event */
    $event = Event::factory()->create([
        'dep' => $depAirport->id,
        'arr' => $depAirport->id,
    ]);

    Http::fake([
        AirportImporter::SOURCE_URL => Http::response(
            "icao,iata,name,city,subd,country,elevation,lat,lon,tz,lid\n" .
            "EGLL,LHR,London Heathrow,London,,GB,83,51.4706,-0.461941,Europe/London,\n"
        ),
    ]);

    $csvContent = "origin,destination,call_sign,aircraft_type\n";
    $csvContent .= "EHAM,EGLL,KLM01,B738\n";

    $file = UploadedFile::fake()->createWithContent('bookings.csv', $csvContent);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.import.store', $event), [
            'file' => $file,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    $this->assertDatabaseHas('airports', ['icao' => 'EGLL', 'iata' => 'LHR']);
    expect(Booking::where('event_id', $event->id)->count())->toBe(1);
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
