<?php

use App\Models\Airport;
use App\Services\AirportImporter;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

function fakeAirportsDataset(string $rows): void
{
    Http::fake([
        AirportImporter::SOURCE_URL => Http::response(
            "icao,iata,name,city,subd,country,elevation,lat,lon,tz,lid\n" . $rows
        ),
    ]);
}

it('creates missing airports from the dataset', function (): void {
    /** @var TestCase $this */
    Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS']);

    fakeAirportsDataset(
        "EGLL,LHR,London Heathrow,London,,GB,83,51.4706,-0.461941,Europe/London,\n" .
        "KSEA,SEA,Seattle Tacoma Intl,Seattle,,US,433,47.449,-122.309306,America/Los_Angeles,\n"
    );

    $result = app(AirportImporter::class)->ensure(['EHAM', 'EGLL', 'KSEA']);

    expect($result['created'])->toEqualCanonicalizing(['EGLL', 'KSEA'])
        ->and($result['unresolved'])->toBe([]);

    $this->assertDatabaseHas('airports', ['icao' => 'EGLL', 'iata' => 'LHR', 'name' => 'London Heathrow']);
    $this->assertDatabaseHas('airports', ['icao' => 'KSEA', 'iata' => 'SEA']);
});

it('does not fetch the dataset when all airports already exist', function (): void {
    /** @var TestCase $this */
    Airport::factory()->create(['icao' => 'EHAM']);
    Http::fake();

    $result = app(AirportImporter::class)->ensure(['EHAM', 'eham', ' EHAM ']);

    expect($result['created'])->toBe([])
        ->and($result['unresolved'])->toBe([]);
    Http::assertNothingSent();
});

it('marks unknown and blank-iata airports as unresolved', function (): void {
    /** @var TestCase $this */
    fakeAirportsDataset(
        "EHGG,,Groningen Eelde,Eelde,,NL,17,53.1197,6.5794,Europe/Amsterdam,\n"
    );

    $result = app(AirportImporter::class)->ensure(['EHGG', 'ZZZZ']);

    expect($result['created'])->toBe([])
        ->and($result['unresolved'])->toEqualCanonicalizing(['EHGG', 'ZZZZ'])
        ->and(Airport::count())->toBe(0);
});
