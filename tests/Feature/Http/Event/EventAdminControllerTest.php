<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from accessing event admin index', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->get(route('admin.events.index'))
        ->assertRedirect('/');
});

it('allows admin users to view event index', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.index'))
        ->assertOk();
});

it('allows admin users to view create event form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.create'))
        ->assertOk();
});

it('allows admin users to view event details', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.show', $event))
        ->assertOk()
        ->assertSee($event->name);
});

it('allows admin users to view edit event form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.edit', $event))
        ->assertOk();
});

it('allows admin users to delete events', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.events.destroy', $event))
        ->assertRedirect();

    $this->assertDatabaseMissing('events', [
        'id' => $event->id,
    ]);
});

it('allows admin users to view send email form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.email.form', $event))
        ->assertOk();
});

it('allows admin users to delete all bookings for an event', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $booking1 = Booking::factory()->create(['event_id' => $event->id]);
    $booking2 = Booking::factory()->create(['event_id' => $event->id]);

    $this->actingAs($admin)
        ->delete(route('admin.events.delete-bookings', $event))
        ->assertRedirect();

    $this->assertDatabaseMissing('bookings', ['id' => $booking1->id]);
    $this->assertDatabaseMissing('bookings', ['id' => $booking2->id]);
});
