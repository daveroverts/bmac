<?php

use App\Models\Event;
use App\Models\Faq;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from attaching an event to a FAQ', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Faq $faq */
    $faq = Faq::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->from('/')
        ->post(route('admin.faq.events.attach', ['faq' => $faq, 'event' => $event]))
        ->assertRedirect('/');
});

it('prevents non-admin users from detaching an event from a FAQ', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Faq $faq */
    $faq = Faq::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();
    $faq->events()->attach($event->id);

    $this->actingAs($user)
        ->from('/')
        ->delete(route('admin.faq.events.detach', ['faq' => $faq, 'event' => $event]))
        ->assertRedirect('/');
});

it('allows admin users to attach an event to a FAQ', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Faq $faq */
    $faq = Faq::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.faq.events.attach', ['faq' => $faq, 'event' => $event]))
        ->assertRedirect();

    $this->assertDatabaseHas('event_faq', [
        'faq_id' => $faq->id,
        'event_id' => $event->id,
    ]);
});

it('allows admin users to detach an event from a FAQ', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Faq $faq */
    $faq = Faq::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();
    $faq->events()->attach($event->id);

    $this->actingAs($admin)
        ->delete(route('admin.faq.events.detach', ['faq' => $faq, 'event' => $event]))
        ->assertRedirect();

    $this->assertDatabaseMissing('event_faq', [
        'faq_id' => $faq->id,
        'event_id' => $event->id,
    ]);
});
