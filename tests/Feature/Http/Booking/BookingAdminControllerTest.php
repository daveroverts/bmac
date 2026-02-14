<?php

use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from accessing booking create form', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->get(route('admin.bookings.create', $event))
        ->assertRedirect('/');
});

it('allows admin users to view create booking form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.bookings.create', $event))
        ->assertOk();
});

it('allows admin users to view edit booking form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.bookings.edit', $flight->booking))
        ->assertOk();
});

it('allows admin users to view auto-assign form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.bookings.autoAssign.create', $event))
        ->assertOk();
});

it('allows admin users to view import form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.bookings.import.create', $event))
        ->assertOk();
});

it('allows admin users to view route assign form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.bookings.routeAssign.create', $event))
        ->assertOk();
});

it('allows admin users to export bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.bookings.export', $event))
        ->assertOk();
});
