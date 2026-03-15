<?php

use App\Models\Airport;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

it('returns airports formatted for dropdown', function (): void {
    /** @var TestCase $this */

    /** @var Airport $airport */
    $airport = Airport::factory()->create();

    $result = Airport::forDropdown();

    expect($result)->toHaveKey($airport->id)
        ->and($result->get($airport->id))->toBe(
            sprintf('%s | %s | %s', $airport->icao, $airport->name, $airport->iata)
        );
});

it('caches the dropdown results', function (): void {
    Airport::factory()->create();

    Cache::flush();
    expect(Cache::has(Airport::CACHE_KEY_DROPDOWN))->toBeFalse();

    Airport::forDropdown();
    expect(Cache::has(Airport::CACHE_KEY_DROPDOWN))->toBeTrue();
});

it('invalidates cache when an airport is saved', function (): void {
    Airport::factory()->create();
    Airport::forDropdown();
    expect(Cache::has(Airport::CACHE_KEY_DROPDOWN))->toBeTrue();

    Airport::factory()->create();
    expect(Cache::has(Airport::CACHE_KEY_DROPDOWN))->toBeFalse();
});

it('invalidates cache when an airport is deleted', function (): void {
    /** @var TestCase $this */

    /** @var Airport $airport */
    $airport = Airport::factory()->create();
    Airport::forDropdown();
    expect(Cache::has(Airport::CACHE_KEY_DROPDOWN))->toBeTrue();

    $airport->delete();
    expect(Cache::has(Airport::CACHE_KEY_DROPDOWN))->toBeFalse();
});
