<?php

use App\Models\Event;
use App\Models\EventLink;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from accessing event link admin index', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.eventLinks.index'))
        ->assertForbidden();
});

it('allows admin users to view event link index', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.eventLinks.index'))
        ->assertOk();
});

it('allows admin users to view create event link form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('admin.eventLinks.create'))
        ->assertOk();
});

it('allows admin users to create event links', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.eventLinks.store'), [
            'event_id' => $event->id,
            'event_link_type_id' => 1,
            'url' => 'https://example.com',
        ])
        ->assertRedirect();

    $this->assertDatabaseHas('event_links', [
        'event_id' => $event->id,
        'url' => 'https://example.com',
    ]);
});

it('allows admin users to view edit event link form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var EventLink $eventLink */
    $eventLink = EventLink::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.eventLinks.edit', $eventLink))
        ->assertOk();
});

it('allows admin users to update event links', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var EventLink $eventLink */
    $eventLink = EventLink::factory()->create([
        'url' => 'https://original.com',
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.eventLinks.update', $eventLink), [
            'event_id' => $eventLink->event_id,
            'event_link_type_id' => $eventLink->event_link_type_id,
            'url' => 'https://updated.com',
        ])
        ->assertRedirect();

    $eventLink->refresh();
    expect($eventLink->url)->toBe('https://updated.com');
});

it('allows admin users to delete event links', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var EventLink $eventLink */
    $eventLink = EventLink::factory()->create();

    $this->actingAs($admin)
        ->delete(route('admin.eventLinks.destroy', $eventLink))
        ->assertRedirect();

    $this->assertDatabaseMissing('event_links', [
        'id' => $eventLink->id,
    ]);
});
