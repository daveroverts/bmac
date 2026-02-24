<?php

use App\Models\Event;
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
