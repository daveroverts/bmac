<?php

use App\Models\Airport;
use App\Models\Flight;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from accessing airport admin index', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->get(route('admin.airports.index'))
        ->assertRedirect('/');
});

it('allows admin users to view airport index', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.airports.index'))
        ->assertOk();
});

it('allows admin users to view create airport form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.airports.create'))
        ->assertOk();
});

it('allows admin users to create airports', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->post(route('admin.airports.store'), [
            'icao' => 'EHAM',
            'iata' => 'AMS',
            'name' => 'Amsterdam Airport Schiphol',
            'latitude' => '52.308601',
            'longitude' => '4.763889',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('airports', [
        'icao' => 'EHAM',
        'iata' => 'AMS',
    ]);
});

it('allows admin users to view airport details', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Airport $airport */
    $airport = Airport::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.airports.show', $airport))
        ->assertOk()
        ->assertSee($airport->name);
});

it('allows admin users to view edit airport form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Airport $airport */
    $airport = Airport::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.airports.edit', $airport))
        ->assertOk();
});

it('allows admin users to update airports', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Airport $airport */
    $airport = Airport::factory()->create([
        'name' => 'Original Name',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.airports.update', $airport), [
            'icao' => $airport->icao,
            'iata' => $airport->iata,
            'name' => 'Updated Name',
            'latitude' => $airport->latitude,
            'longitude' => $airport->longitude,
        ])
        ->assertRedirect();

    $airport->refresh();
    expect($airport->name)->toBe('Updated Name');
});

it('allows admin users to delete airports', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Airport $airport */
    $airport = Airport::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.airports.destroy', $airport))
        ->assertRedirect();

    $this->assertDatabaseMissing('airports', [
        'id' => $airport->id,
    ]);
});

it('allows admin users to destroy unused airports', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Airport $usedAirportDep */
    $usedAirportDep = Airport::factory()->create();

    /** @var Airport $usedAirportArr */
    $usedAirportArr = Airport::factory()->create();

    /** @var Airport $unusedAirport */
    $unusedAirport = Airport::factory()->create();

    Flight::factory()->create([
        'dep' => $usedAirportDep->id,
        'arr' => $usedAirportArr->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.airports.destroyUnused'))
        ->assertRedirect(route('admin.airports.index'));

    $this->assertDatabaseHas('airports', ['id' => $usedAirportDep->id]);
    $this->assertDatabaseHas('airports', ['id' => $usedAirportArr->id]);
    $this->assertDatabaseMissing('airports', ['id' => $unusedAirport->id]);
});
