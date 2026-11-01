<?php

use App\Enums\EventType;
use App\Models\Event;
use Illuminate\Support\Facades\Date;

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

it('resolves slot times to the date nearest the event window', function (string $startEvent, string $endEvent, string $time, string $expected): void {
    $event = Event::factory()->create(['startEvent' => $startEvent, 'endEvent' => $endEvent]);

    expect($event->resolveSlotTime($time)->format('Y-m-d H:i'))->toBe($expected);
})->with([
    'evening, before midnight' => ['2026-07-15 22:00:00', '2026-07-16 02:00:00', '23:30', '2026-07-15 23:30'],
    'evening, after midnight' => ['2026-07-15 22:00:00', '2026-07-16 02:00:00', '00:30', '2026-07-16 00:30'],
    'evening, just before start' => ['2026-07-15 22:00:00', '2026-07-16 02:00:00', '21:00', '2026-07-15 21:00'],
    'daytime, before start' => ['2026-07-15 10:00:00', '2026-07-15 14:00:00', '09:00', '2026-07-15 09:00'],
]);

it('resolves a time to its first occurrence at or after a reference', function (string $time, string $expected): void {
    $event = Event::factory()->create();

    $after = Date::parse('2026-07-15 23:30');

    expect($event->resolveTimeAfter($time, $after)->format('Y-m-d H:i'))->toBe($expected);
})->with([
    'next morning' => ['06:00', '2026-07-16 06:00'],
    'later same day' => ['23:45', '2026-07-15 23:45'],
]);
