<?php

use App\Models\AirportLinkType;
use Illuminate\Support\Facades\Cache;

it('returns airport link types formatted for dropdown', function (): void {
    $result = AirportLinkType::forDropdown();

    expect($result)->not->toBeEmpty();
});

it('caches the dropdown results', function (): void {
    Cache::flush();
    expect(Cache::has(AirportLinkType::CACHE_KEY_DROPDOWN))->toBeFalse();

    AirportLinkType::forDropdown();
    expect(Cache::has(AirportLinkType::CACHE_KEY_DROPDOWN))->toBeTrue();
});

it('invalidates cache when an airport link type is saved', function (): void {
    AirportLinkType::forDropdown();
    expect(Cache::has(AirportLinkType::CACHE_KEY_DROPDOWN))->toBeTrue();

    /** @var AirportLinkType $airportLinkType */
    $airportLinkType = AirportLinkType::first();
    $airportLinkType->update(['name' => 'Updated Name']);

    expect(Cache::has(AirportLinkType::CACHE_KEY_DROPDOWN))->toBeFalse();
});
