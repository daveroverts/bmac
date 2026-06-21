<?php

use App\Livewire\Booking\Admin\Import;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Services\AirportImporter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

beforeEach(function (): void {
    /** @var TestCase $this */
    Cache::flush();

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
});

function importCsv(string $content): UploadedFile
{
    return UploadedFile::fake()->createWithContent('bookings.csv', $content);
}

function fakeImportDataset(string $rows = ''): void
{
    Http::fake([
        AirportImporter::SOURCE_URL => Http::response(
            "icao,iata,name,city,subd,country,elevation,lat,lon,tz,lid\n" . $rows
        ),
    ]);
}

it('parses rows and pre-selects only the valid ones', function (): void {
    Airport::factory()->create(['icao' => 'EHAM']);
    Airport::factory()->create(['icao' => 'EGLL']);
    $event = Event::factory()->create();
    fakeImportDataset(); // ZZZZ is not in the dataset

    $file = importCsv(
        "origin,destination,call_sign,aircraft_type\n" .
        "EHAM,EGLL,KLM01,B738\n" .
        "EHAM,ZZZZ,KLM02,B738\n"
    );

    Livewire::test(Import::class, ['event' => $event])
        ->set('file', $file)
        ->assertCount('rows', 2)
        ->assertSet('selected', [0]);
});

it('imports only the selected valid rows', function (): void {
    /** @var TestCase $this */
    Airport::factory()->create(['icao' => 'EHAM']);
    Airport::factory()->create(['icao' => 'EGLL']);
    $event = Event::factory()->create();

    $file = importCsv(
        "origin,destination,call_sign,aircraft_type\n" .
        "EHAM,EGLL,KLM01,B738\n" .
        "EGLL,EHAM,KLM02,B738\n"
    );

    Livewire::test(Import::class, ['event' => $event])
        ->set('file', $file)
        ->set('selected', [0])
        ->call('import')
        ->assertRedirect(route('events.bookings.index', $event));

    expect(Booking::where('event_id', $event->id)->count())->toBe(1);
    $this->assertDatabaseHas('bookings', ['callsign' => 'KLM01']);
});

it('imports flight times', function (): void {
    Airport::factory()->create(['icao' => 'EHAM']);
    Airport::factory()->create(['icao' => 'EGLL']);
    $event = Event::factory()->create(['startEvent' => '2026-07-15 00:00:00']);

    $file = importCsv(
        "origin,destination,call_sign,aircraft_type,ctot,eta\n" .
        "EHAM,EGLL,KLM01,B738,14:30,15:45\n"
    );

    Livewire::test(Import::class, ['event' => $event])
        ->set('file', $file)
        ->call('import');

    $flight = Booking::where('event_id', $event->id)->firstOrFail()->flights()->firstOrFail();

    expect($flight->ctot->format('Y-m-d H:i'))->toBe('2026-07-15 14:30')
        ->and($flight->eta->format('Y-m-d H:i'))->toBe('2026-07-15 15:45');
});

it('auto-creates missing airports while parsing', function (): void {
    /** @var TestCase $this */
    Airport::factory()->create(['icao' => 'EHAM']);
    $event = Event::factory()->create();

    fakeImportDataset("EGLL,LHR,London Heathrow,London,,GB,83,51.4706,-0.461941,Europe/London,\n");

    $file = importCsv("origin,destination,call_sign,aircraft_type\nEHAM,EGLL,KLM01,B738\n");

    Livewire::test(Import::class, ['event' => $event])
        ->set('file', $file)
        ->assertSet('autoAddedAirports', ['EGLL'])
        ->assertSet('selected', [0]);

    $this->assertDatabaseHas('airports', ['icao' => 'EGLL', 'iata' => 'LHR']);
});

it('toggles selection with select all and select none', function (): void {
    Airport::factory()->create(['icao' => 'EHAM']);
    Airport::factory()->create(['icao' => 'EGLL']);
    $event = Event::factory()->create();

    $file = importCsv("origin,destination\nEHAM,EGLL\nEGLL,EHAM\n");

    Livewire::test(Import::class, ['event' => $event])
        ->set('file', $file)
        ->call('selectNone')
        ->assertSet('selected', [])
        ->call('selectAll')
        ->assertSet('selected', [0, 1]);
});

it('rejects a disallowed file type', function (): void {
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->create('bookings.txt', 100, 'text/plain');

    Livewire::test(Import::class, ['event' => $event])
        ->set('file', $file)
        ->assertHasErrors(['file']);
});
