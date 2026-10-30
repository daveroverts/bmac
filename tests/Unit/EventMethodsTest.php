<?php

use App\Enums\EventType;
use App\Models\Event;

it('only disallows manual bookings for MULTIFLIGHTS events', function (EventType $type, bool $expected): void {
    $event = Event::factory()->create(['event_type_id' => $type->value]);

    expect($event->allowsManualBookings())->toBe($expected);
})->with([
    'ONEWAY' => [EventType::ONEWAY, true],
    'CITYPAIR' => [EventType::CITYPAIR, true],
    'FLYIN' => [EventType::FLYIN, true],
    'GROUPFLIGHT' => [EventType::GROUPFLIGHT, true],
    'MULTIFLIGHTS' => [EventType::MULTIFLIGHTS, false],
]);

it('returns true from hasOrderButtons for FLYIN event type', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::FLYIN->value]);

    expect($event->hasOrderButtons())->toBeTrue();
});

it('returns true from hasOrderButtons for GROUPFLIGHT event type', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::GROUPFLIGHT->value]);

    expect($event->hasOrderButtons())->toBeTrue();
});

it('returns false from hasOrderButtons for non-FLYIN/GROUPFLIGHT event types', function (EventType $type): void {
    $event = Event::factory()->create(['event_type_id' => $type->value]);

    expect($event->hasOrderButtons())->toBeFalse();
})->with([
    'ONEWAY' => EventType::ONEWAY,
    'CITYPAIR' => EventType::CITYPAIR,
    'MULTIFLIGHTS' => EventType::MULTIFLIGHTS,
]);
