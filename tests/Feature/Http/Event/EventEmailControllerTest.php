<?php

use App\Events\EventBulkEmail;
use App\Events\EventFinalInformation;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Event as EventFacade;
use Tests\TestCase;

it('prevents non-admin users from accessing send email form', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.events.emails.bulk.create', $event))
        ->assertForbidden();
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
        ->post(route('admin.events.emails.bulk.send', $event), [
            'subject' => 'Test Subject',
            'message' => 'Test message',
        ])
        ->assertForbidden();
});

it('prevents non-admin users from sending final information email', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.events.emails.final.send', $event))
        ->assertForbidden();
});

it('returns 422 when sending final information test email with no booked bookings', function (): void {
    /** @var TestCase $this */

    EventFacade::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->postJson(route('admin.events.emails.final.send', $event), ['testmode' => true])
        ->assertUnprocessable()
        ->assertJson(['error' => __('No booked bookings found for this event')]);

    EventFacade::assertNotDispatched(EventFinalInformation::class);
});

it('sends a test final information email to the admin when booked bookings exist', function (): void {
    /** @var TestCase $this */

    EventFacade::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.events.emails.final.send', $event), ['testmode' => true])
        ->assertOk()
        ->assertJson(['success' => __('Email has been sent to yourself')]);

    EventFacade::assertDispatched(EventFinalInformation::class);
});

it('sends bulk email to all users with booked bookings', function (): void {
    /** @var TestCase $this */

    EventFacade::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Booking::factory()->booked()->create(['event_id' => $event->id, 'user_id' => $user1->id]);
    Booking::factory()->booked()->create(['event_id' => $event->id, 'user_id' => $user2->id]);

    $this->actingAs($admin)
        ->post(route('admin.events.emails.bulk.send', $event), [
            'subject' => 'Test Subject',
            'message' => 'Test message body',
        ])
        ->assertRedirect(route('admin.events.index'));

    EventFacade::assertDispatched(EventBulkEmail::class, fn (EventBulkEmail $e): bool => $e->users->count() === 2
        && $e->users->pluck('id')->contains($user1->id)
        && $e->users->pluck('id')->contains($user2->id));
});

it('sends bulk test email only to the admin', function (): void {
    /** @var TestCase $this */

    EventFacade::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->postJson(route('admin.events.emails.bulk.send', $event), [
            'subject' => 'Test Subject',
            'message' => 'Test message body',
            'testmode' => true,
        ])
        ->assertOk()
        ->assertJson(['success' => __('Email has been sent to yourself')]);

    EventFacade::assertDispatched(EventBulkEmail::class, fn (EventBulkEmail $e): bool => $e->users->count() === 1 && $e->users->first()->id === $admin->id);
});

it('sends final information email to all booked bookings', function (): void {
    /** @var TestCase $this */

    EventFacade::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    Booking::factory()->booked()->create(['event_id' => $event->id, 'user_id' => $user1->id]);
    Booking::factory()->booked()->create(['event_id' => $event->id, 'user_id' => $user2->id]);

    $this->actingAs($admin)
        ->post(route('admin.events.emails.final.send', $event))
        ->assertRedirect(route('admin.events.index'));

    EventFacade::assertDispatchedTimes(EventFinalInformation::class, 2);
});

it('skips bookings that already received final information email', function (): void {
    /** @var TestCase $this */

    EventFacade::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
        'final_information_email_sent_at' => now(),
    ]);
    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.events.emails.final.send', $event))
        ->assertRedirect(route('admin.events.index'));

    EventFacade::assertDispatchedTimes(EventFinalInformation::class, 1);
});

it('sends final information to all bookings when forceSend is set', function (): void {
    /** @var TestCase $this */

    EventFacade::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
        'final_information_email_sent_at' => now(),
    ]);
    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);

    $this->actingAs($admin)
        ->post(route('admin.events.emails.final.send', $event), ['forceSend' => true])
        ->assertRedirect(route('admin.events.index'));

    EventFacade::assertDispatchedTimes(EventFinalInformation::class, 2);
});
