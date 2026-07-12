<?php

use App\Imports\AirportsImport;
use App\Models\Airport;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

function fakeAirportsCsv(string $rows): string
{
    $file = 'airports_test.csv';
    Storage::fake('local')->put(
        $file,
        "icao,iata,name,city,subd,country,elevation,lat,lon,tz,lid\n" . $rows
    );

    return $file;
}

it('imports airports from a CSV file', function (): void {
    /** @var TestCase $this */
    $file = fakeAirportsCsv(
        "EGLL,LHR,London Heathrow,London,,GB,83,51.4706,-0.461941,Europe/London,\n" .
        "KSEA,SEA,Seattle Tacoma Intl,Seattle,,US,433,47.449,-122.309306,America/Los_Angeles,\n"
    );

    (new AirportsImport())->import($file, 'local');

    $this->assertDatabaseHas('airports', [
        'icao' => 'EGLL',
        'iata' => 'LHR',
        'name' => 'London Heathrow',
        'latitude' => 51.4706,
        'longitude' => -0.461941,
    ]);
    $this->assertDatabaseHas('airports', ['icao' => 'KSEA', 'iata' => 'SEA']);
    expect(Airport::count())->toBe(2);
});

it('skips rows that already exist or fail validation', function (): void {
    /** @var TestCase $this */
    Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS']);

    $file = fakeAirportsCsv(
        "EHAM,AMS,Amsterdam Schiphol,Amsterdam,,NL,-11,52.308609,4.763889,Europe/Amsterdam,\n" .
        "ZZZZ,ZZZ,Bad Airport,Nowhere,,XX,0,not-a-number,4.763889,UTC,\n" .
        "EGLL,LHR,London Heathrow,London,,GB,83,51.4706,-0.461941,Europe/London,\n"
    );

    (new AirportsImport())->import($file, 'local');

    $this->assertDatabaseHas('airports', ['icao' => 'EGLL']);
    $this->assertDatabaseMissing('airports', ['icao' => 'ZZZZ']);
    expect(Airport::count())->toBe(2);
});
