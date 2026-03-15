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

it('filters upcoming events using scope', function (): void {
    $futureEvent = Event::factory()->create([
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    Event::factory()->expired()->create();

    $results = Event::query()->upcoming()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->id)->toBe($futureEvent->id);
});

it('orders upcoming events by startEvent ascending', function (): void {
    $soonest = Event::factory()->create([
        'startEvent' => now()->addDays(3),
        'endEvent' => now()->addDays(3)->addHours(3),
    ]);

    $later = Event::factory()->create([
        'startEvent' => now()->addDays(10),
        'endEvent' => now()->addDays(10)->addHours(3),
    ]);

    $results = Event::query()->upcoming()->get();

    expect($results->first()->id)->toBe($soonest->id)
        ->and($results->last()->id)->toBe($later->id);
});

it('filters online events using scope', function (): void {
    Event::factory()->create(['is_online' => true]);
    Event::factory()->create(['is_online' => false]);

    $results = Event::query()->online()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->is_online)->toBeTrue();
});

it('filters homepage events using scope', function (): void {
    Event::factory()->onHomePage()->create();
    Event::factory()->notOnHomePage()->create();

    $results = Event::query()->onHomepage()->get();

    expect($results)->toHaveCount(1)
        ->and($results->first()->show_on_homepage)->toBeTrue();
});

it('chains upcoming, online, and homepage scopes', function (): void {
    Event::factory()->onHomePage()->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    Event::factory()->notOnHomePage()->create([
        'is_online' => true,
        'startEvent' => now()->addWeek(),
        'endEvent' => now()->addWeek()->addHours(3),
    ]);

    Event::factory()->expired()->create(['is_online' => true]);

    $results = Event::query()->upcoming()->online()->onHomepage()->get();

    expect($results)->toHaveCount(1);
});
