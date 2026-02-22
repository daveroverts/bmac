<?php

use App\Models\Airport;
use Tests\TestCase;

it('returns a paginated list of all airports', function (): void {
    /** @var TestCase $this */

    Airport::factory()->count(3)->create();

    $this->getJson('/api/v1/airports')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns a single airport by icao', function (): void {
    /** @var TestCase $this */

    $airport = Airport::factory()->create();

    $this->getJson("/api/v1/airports/{$airport->icao}")
        ->assertOk()
        ->assertJsonPath('data.icao', $airport->icao)
        ->assertJsonPath('data.name', $airport->name);
});

it('returns 404 for a non-existent airport', function (): void {
    /** @var TestCase $this */

    $this->getJson('/api/v1/airports/ZZZZ')
        ->assertNotFound();
});

it('does not include deprecation headers on v1 airport routes', function (): void {
    /** @var TestCase $this */

    $this->getJson('/api/v1/airports')
        ->assertOk()
        ->assertHeaderMissing('Deprecation')
        ->assertHeaderMissing('Sunset');
});
