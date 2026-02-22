<?php

use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from accessing send email form', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->get(route('admin.events.emails.bulk.create', $event))
        ->assertRedirect('/');
});

it('allows admin users to view send email form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.emails.bulk.create', $event))
        ->assertOk();
});

it('prevents non-admin users from sending bulk email', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->post(route('admin.events.emails.bulk.send', $event), [
            'subject' => 'Test Subject',
            'message' => 'Test message',
        ])
        ->assertRedirect('/');
});

it('prevents non-admin users from sending final information email', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->post(route('admin.events.emails.final.send', $event))
        ->assertRedirect('/');
});
