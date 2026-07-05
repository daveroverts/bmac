<?php

use App\Livewire\Airport\Admin\Overview;
use App\Models\Airport;
use App\Models\User;
use Livewire\Livewire;

it('returns 404 for non-admin users', function (): void {
    /** @var \App\Models\User $user */
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Overview::class)
        ->assertStatus(404);
});

it('lists airports for admin users', function (): void {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->admin()->create();

    $airport = Airport::factory()->create(['name' => 'Amsterdam Airport Schiphol']);

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->assertSee($airport->icao)
        ->assertSee('Amsterdam Airport Schiphol');
});

it('filters airports by icao, iata or name', function (): void {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->admin()->create();

    $schiphol = Airport::factory()->create(['icao' => 'EHAM', 'iata' => 'AMS', 'name' => 'Amsterdam Airport Schiphol']);
    $rotterdam = Airport::factory()->create(['icao' => 'EHRD', 'iata' => 'RTM', 'name' => 'Rotterdam The Hague Airport']);

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->set('search', 'EHAM')
        ->assertSee($schiphol->name)
        ->assertDontSee($rotterdam->name)
        ->set('search', 'RTM')
        ->assertSee($rotterdam->name)
        ->assertDontSee($schiphol->name)
        ->set('search', 'Schiphol')
        ->assertSee($schiphol->name)
        ->assertDontSee($rotterdam->name);
});

it('resets pagination when searching', function (): void {
    /** @var \App\Models\User $admin */
    $admin = User::factory()->admin()->create();

    Livewire::actingAs($admin)
        ->test(Overview::class)
        ->set('paginators.page', 2)
        ->set('search', 'EH')
        ->assertSet('paginators.page', 1);
});
