<?php

namespace Tests\Unit;

use App\Models\Airport;
use App\Models\Event;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Tests\TestCase;

it('creates new event', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->assertDatabaseHas('events', [
        'id' => $event->id,
        'name' => $event->name,
        'slug' => $event->slug,
        'description' => $event->description,
        'dep' => $event->dep,
        'arr' => $event->arr,
        'startEvent' => $event->startEvent,
        'endEvent' => $event->endEvent,
        'startBooking' => $event->startBooking,
        'endBooking' => $event->endBooking,
    ]);
});

it('has BelongsTo relationship for type', function (): void {
    $event = Event::factory()->create();

    expect($event->type())->toBeInstanceOf(BelongsTo::class);
});

it('has BelongsTo relationships for airportDep and airportArr', function (): void {
    $event = Event::factory()->create();

    expect($event->airportDep())->toBeInstanceOf(BelongsTo::class);
    expect($event->airportArr())->toBeInstanceOf(BelongsTo::class);
});

it('resolves airportDep and airportArr to correct Airport models', function (): void {
    $dep = Airport::factory()->create();
    $arr = Airport::factory()->create();

    $event = Event::factory()->create(['dep' => $dep->id, 'arr' => $arr->id]);

    expect($event->airportDep->id)->toBe($dep->id);
    expect($event->airportArr->id)->toBe($arr->id);
});

it('has HasMany relationship for bookings', function (): void {
    $event = Event::factory()->create();

    expect($event->bookings())->toBeInstanceOf(HasMany::class);
});
