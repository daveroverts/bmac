<?php

use App\Models\Airport;
use Tests\TestCase;

it('returns a paginated list of all airports on legacy route', function (): void {
    /** @var TestCase $this */

    Airport::factory()->count(3)->create();

    $this->getJson('/api/airports')
        ->assertOk()
        ->assertJsonStructure(['data'])
        ->assertHeader('Deprecation', 'true')
        ->assertHeader('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT');
});

it('returns a single airport by icao on legacy route', function (): void {
    /** @var TestCase $this */

    /** @var Airport $airport */
    $airport = Airport::factory()->create();

    $this->getJson('/api/airports/' . $airport->icao)
        ->assertOk()
        ->assertJsonPath('data.icao', $airport->icao)
        ->assertJsonPath('data.name', $airport->name)
        ->assertHeader('Deprecation', 'true')
        ->assertHeader('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT');
});

it('includes deprecation headers on legacy airport routes', function (): void {
    /** @var TestCase $this */

    $this->getJson('/api/airports')
        ->assertOk()
        ->assertHeader('Deprecation', 'true')
        ->assertHeader('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT')
        ->assertHeader('Link');
});
