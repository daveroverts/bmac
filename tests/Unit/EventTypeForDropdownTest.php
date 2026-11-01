<?php

use App\Models\EventType;
use Illuminate\Support\Facades\Cache;

it('returns event types formatted for dropdown', function (): void {
    $result = EventType::forDropdown();

    expect($result)->not->toBeEmpty();
});

it('caches the dropdown results', function (): void {
    Cache::flush();
    expect(Cache::has(EventType::CACHE_KEY_DROPDOWN))->toBeFalse();

    EventType::forDropdown();
    expect(Cache::has(EventType::CACHE_KEY_DROPDOWN))->toBeTrue();
});

it('invalidates cache when an event type is saved', function (): void {
    EventType::forDropdown();
    expect(Cache::has(EventType::CACHE_KEY_DROPDOWN))->toBeTrue();

    /** @var EventType $eventType */
    $eventType = EventType::first();
    $eventType->update(['name' => 'Updated Name']);

    expect(Cache::has(EventType::CACHE_KEY_DROPDOWN))->toBeFalse();
});

it('caches a plain array instead of a Collection object', function (): void {
    Cache::flush();

    $result = EventType::forDropdown();

    expect(Cache::get(EventType::CACHE_KEY_DROPDOWN))->toBeArray()
        ->and(EventType::forDropdown()->all())->toBe($result->all());
});
