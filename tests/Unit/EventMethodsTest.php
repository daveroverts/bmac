<?php

use App\Enums\EventType;
use App\Models\Event;

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
