<?php

use App\Models\Event;
use Tests\TestCase;

it('returns a paginated list of all events', function (): void {
    /** @var TestCase $this */

    Event::factory()->count(3)->create();

    $this->getJson('/api/v1/events')
        ->assertOk()
        ->assertJsonStructure(['data']);
});

it('returns a single event by slug', function (): void {
    /** @var TestCase $this */

    $event = Event::factory()->create();

    $this->getJson("/api/v1/events/{$event->slug}")
        ->assertOk()
        ->assertJsonPath('data.slug', $event->slug)
        ->assertJsonPath('data.name', $event->name)
        ->assertJsonPath('data.links.bookings', route('v1.events.bookings.index', $event))
        ->assertJsonPath('data.links.dep', route('v1.airports.show', $event->airportDep))
        ->assertJsonPath('data.links.arr', route('v1.airports.show', $event->airportArr));
});

it('returns 404 for a non-existent event', function (): void {
    /** @var TestCase $this */

    $this->getJson('/api/v1/events/non-existent-slug')
        ->assertNotFound();
});

it('returns upcoming online events with default limit of 3', function (): void {
    /** @var TestCase $this */

    Event::factory()->count(5)->create([
        'is_online' => true,
        'endEvent' => now()->addMonth(),
    ]);

    $response = $this->getJson('/api/v1/events/upcoming')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});

it('returns upcoming online events with a custom limit', function (): void {
    /** @var TestCase $this */

    Event::factory()->count(5)->create([
        'is_online' => true,
        'endEvent' => now()->addMonth(),
    ]);

    $response = $this->getJson('/api/v1/events/upcoming/2')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(2);
});

it('excludes offline events from upcoming', function (): void {
    /** @var TestCase $this */

    Event::factory()->create(['is_online' => false, 'endEvent' => now()->addMonth()]);
    Event::factory()->create(['is_online' => true, 'endEvent' => now()->addMonth()]);

    $response = $this->getJson('/api/v1/events/upcoming/10')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('excludes past events from upcoming', function (): void {
    /** @var TestCase $this */

    Event::factory()->expired()->create(['is_online' => true]);
    Event::factory()->create(['is_online' => true, 'endEvent' => now()->addMonth()]);

    $response = $this->getJson('/api/v1/events/upcoming/10')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('does not include deprecation headers on v1 event routes', function (): void {
    /** @var TestCase $this */

    $this->getJson('/api/v1/events')
        ->assertOk()
        ->assertHeaderMissing('Deprecation')
        ->assertHeaderMissing('Sunset');
});
