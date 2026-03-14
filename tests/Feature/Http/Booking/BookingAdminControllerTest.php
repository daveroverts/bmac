<?php

use App\Events\BookingChanged;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event as EventFacade;
use Tests\TestCase;

it('prevents non-admin users from accessing booking create form', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.events.bookings.create', $event))
        ->assertForbidden();
});

it('allows admin users to view create booking form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.bookings.create', $event))
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
        ->get(route('admin.events.bookings.autoAssign.create', $event))
        ->assertOk();
});

it('allows admin users to view import form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.bookings.import.create', $event))
        ->assertOk();
});

it('allows admin users to view route assign form', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.bookings.routeAssign.create', $event))
        ->assertOk();
});

it('allows admin users to export bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', $event))
        ->assertOk();
});

it('prevents non-admin users from deleting all bookings for an event', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->delete(route('admin.events.bookings.destroyAll', $event))
        ->assertForbidden();
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
        ->delete(route('admin.events.bookings.destroyAll', $event))
        ->assertRedirect();

    $this->assertDatabaseMissing('bookings', ['id' => $booking1->id]);
    $this->assertDatabaseMissing('bookings', ['id' => $booking2->id]);
});

it('allows admin users to store a single booking', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.bookings.store'), [
            'id' => $event->id,
            'bulk' => false,
            'is_editable' => true,
            'callsign' => 'TEST01',
            'acType' => 'B738',
            'dep' => $event->airportDep->id,
            'arr' => $event->airportArr->id,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    $this->assertDatabaseHas('bookings', [
        'event_id' => $event->id,
        'callsign' => 'TEST01',
        'acType' => 'B738',
    ]);
});

it('allows admin users to store bulk bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.bookings.store'), [
            'id' => $event->id,
            'bulk' => true,
            'is_editable' => true,
            'dep' => $event->airportDep->id,
            'arr' => $event->airportArr->id,
            'start' => $event->startEvent->format('H:i'),
            'end' => $event->startEvent->addHour()->format('H:i'),
            'separation' => 10,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    expect(Booking::where('event_id', $event->id)->count())->toBeGreaterThan(0);
});

it('allows admin users to update a booking', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create();

    /** @var Booking $booking */
    $booking = $flight->booking;

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $booking), [
            'is_editable' => true,
            'callsign' => 'EDIT01',
            'acType' => 'A320',
            'dep' => $flight->dep,
            'arr' => $flight->arr,
            'ctot' => null,
            'eta' => null,
        ])
        ->assertRedirect(route('events.bookings.index', $booking->event));

    $booking->refresh();
    expect($booking->callsign)->toBe('EDIT01');
    expect($booking->acType)->toBe('A320');
});

it('sends notification when admin updates a booking with notify_user enabled', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'user_id' => $user->id,
        ])->id,
    ]);

    /** @var Booking $booking */
    $booking = $flight->booking;

    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $booking), [
            'is_editable' => true,
            'callsign' => 'NOTIF1',
            'acType' => 'B744',
            'dep' => $flight->dep,
            'arr' => $flight->arr,
            'ctot' => null,
            'eta' => null,
            'notify_user' => true,
        ])
        ->assertRedirect(route('events.bookings.index', $booking->event));

    EventFacade::assertDispatched(BookingChanged::class);
});

it('does not send notification when admin updates a booking without notify_user', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'user_id' => $user->id,
        ])->id,
    ]);

    /** @var Booking $booking */
    $booking = $flight->booking;

    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $booking), [
            'is_editable' => true,
            'callsign' => 'NOTIF2',
            'acType' => 'B744',
            'dep' => $flight->dep,
            'arr' => $flight->arr,
            'ctot' => null,
            'eta' => null,
        ])
        ->assertRedirect(route('events.bookings.index', $booking->event));

    EventFacade::assertNotDispatched(BookingChanged::class);
});

it('rejects route assign with a disallowed file type', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->create('routes.txt', 100, 'text/plain');

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.routeAssign.store', $event), [
            'file' => $file,
        ])
        ->assertSessionHasErrors('file');
});

it('rejects route assign when file exceeds max size', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $file = UploadedFile::fake()->create('routes.csv', 11000, 'text/csv');

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.routeAssign.store', $event), [
            'file' => $file,
        ])
        ->assertSessionHasErrors('file');
});
