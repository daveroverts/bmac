<?php

use App\Models\Airport;
use App\Models\AirportLink;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from accessing airport link admin index', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.airportLinks.index'))
        ->assertForbidden();
});

it('allows admin users to view airport link index', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.airportLinks.index'))
        ->assertOk();
});

it('allows admin users to view create airport link form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.airportLinks.create'))
        ->assertOk();
});

it('allows admin users to create airport links', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Airport $airport */
    $airport = Airport::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.airportLinks.store'), [
            'airport_id' => $airport->id,
            'airportLinkType_id' => 1,
            'url' => 'https://example.com',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('airport_links', [
        'airport_id' => $airport->id,
        'url' => 'https://example.com',
    ]);
});

it('allows admin users to view edit airport link form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var AirportLink $airportLink */
    $airportLink = AirportLink::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.airportLinks.edit', $airportLink))
        ->assertOk();
});

it('allows admin users to update airport links', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var AirportLink $airportLink */
    $airportLink = AirportLink::factory()->create([
        'url' => 'https://original.com',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.airportLinks.update', $airportLink), [
            'airport_id' => $airportLink->airport_id,
            'airportLinkType_id' => $airportLink->airportLinkType_id,
            'url' => 'https://updated.com',
        ])
        ->assertRedirect();

    $airportLink->refresh();
    expect($airportLink->url)->toBe('https://updated.com');
});

it('allows admin users to delete airport links', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var AirportLink $airportLink */
    $airportLink = AirportLink::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.airportLinks.destroy', $airportLink))
        ->assertRedirect();

    $this->assertSoftDeleted('airport_links', [
        'id' => $airportLink->id,
    ]);
});
