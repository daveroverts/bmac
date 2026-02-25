<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

it('clamps an over-limit request to 50 results on legacy upcoming route', function (): void {
    /** @var TestCase $this */

    Event::factory()->count(60)->create([
        'is_online' => true,
        'endEvent' => now()->addMonth(),
    ]);

    $response = $this->getJson('/api/events/upcoming/999999')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(50);
});

it('clamps a zero or negative limit to 1 result on legacy upcoming route', function (): void {
    /** @var TestCase $this */

    Event::factory()->count(5)->create([
        'is_online' => true,
        'endEvent' => now()->addMonth(),
    ]);

    $response = $this->getJson('/api/events/upcoming/0')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(1);
});

it('returns default limit of 3 on legacy upcoming route', function (): void {
    /** @var TestCase $this */

    Event::factory()->count(5)->create([
        'is_online' => true,
        'endEvent' => now()->addMonth(),
    ]);

    $response = $this->getJson('/api/events/upcoming')
        ->assertOk();

    expect($response->json('data'))->toHaveCount(3);
});

it('includes deprecation headers on legacy upcoming route', function (): void {
    /** @var TestCase $this */

    $response = $this->getJson('/api/events/upcoming')
        ->assertOk();

    $response
        ->assertHeader('Deprecation', 'true')
        ->assertHeader('Sunset', 'Wed, 31 Dec 2026 23:59:59 GMT');
});

it('returns a paginated list of all events on legacy route', function (): void {
    /** @var TestCase $this */

    Event::factory()->count(3)->create();

    $this->getJson('/api/events')
        ->assertOk()
        ->assertJsonStructure(['data'])
        ->assertHeader('Deprecation', 'true');
});

it('returns a single event by slug on legacy route', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->getJson('/api/events/' . $event->slug)
        ->assertOk()
        ->assertJsonPath('data.slug', $event->slug)
        ->assertJsonPath('data.name', $event->name)
        ->assertHeader('Deprecation', 'true');
});

it('returns bookings for an event on legacy route', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->create();

    $user = User::factory()->create();
    $booking = Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'callsign' => 'LEG001',
    ]);
    $booking->flights()->create([
        'dep' => $event->airportDep->id,
        'arr' => $event->airportArr->id,
    ]);

    Booking::factory()->unassigned()->create(['event_id' => $event->id]);

    $response = $this->getJson('/api/events/' . $event->slug . '/bookings')
        ->assertOk()
        ->assertHeader('Deprecation', 'true');

    $data = $response->json('data');
    expect($data)->toHaveCount(1);
    expect($data[0]['callsign'])->toBe('LEG001');
});
