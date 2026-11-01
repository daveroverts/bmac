<?php

use App\Enums\EventType;
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

it('prefills the create booking form with the event departure and arrival airports', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    expect($event->dep)->not->toBe($event->arr);

    $content = $this->actingAs($admin)
        ->get(route('admin.events.bookings.create', $event))
        ->assertOk()
        ->getContent();

    expect($content)
        ->toMatch('/<select name="dep".*?<option value="'.$event->dep.'" selected/s')
        ->toMatch('/<select name="arr".*?<option value="'.$event->arr.'" selected/s');
});

it('redirects to the import page instead of showing the create form for Multi flights events', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create(['event_type_id' => EventType::MULTIFLIGHTS->value]);

    $this->actingAs($admin)
        ->get(route('admin.events.bookings.create', $event))
        ->assertRedirect(route('admin.events.bookings.import.create', $event));
});

it('does not create slots manually for Multi flights events', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create(['event_type_id' => EventType::MULTIFLIGHTS->value]);

    $this->actingAs($admin)
        ->post(route('admin.bookings.store'), [
            'id' => $event->id,
            'bulk' => 0,
            'is_callsign_editable' => 1,
            'is_actype_editable' => 1,
            'dep' => $event->dep,
            'arr' => $event->arr,
        ])
        ->assertRedirect(route('admin.events.bookings.import.create', $event));

    expect($event->bookings()->count())->toBe(0);
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
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
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

it('stores CTOT and ETA after midnight on the next day for an evening event', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create([
        'startEvent' => '2026-07-15 22:00:00',
        'endEvent' => '2026-07-16 02:00:00',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.bookings.store'), [
            'id' => $event->id,
            'bulk' => false,
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
            'dep' => $event->airportDep->id,
            'arr' => $event->airportArr->id,
            'ctot' => '00:30',
            'eta' => '06:00',
        ]);

    $flight = Flight::whereHas('booking', fn ($query) => $query->where('event_id', $event->id))->firstOrFail();

    expect($flight->ctot->format('Y-m-d H:i'))->toBe('2026-07-16 00:30')
        ->and($flight->eta->format('Y-m-d H:i'))->toBe('2026-07-16 06:00');
});

it('rejects a malformed CTOT when storing a booking', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->post(route('admin.bookings.store'), [
            'id' => $event->id,
            'bulk' => false,
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
            'dep' => $event->airportDep->id,
            'arr' => $event->airportArr->id,
            'ctot' => 'abc',
        ])
        ->assertSessionHasErrors('ctot');
});

it('updates CTOT and ETA after midnight on the next day for an evening event', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create([
        'startEvent' => '2026-07-15 22:00:00',
        'endEvent' => '2026-07-16 02:00:00',
    ]);
    $booking = Booking::factory()->create(['event_id' => $event->id]);
    $flight = Flight::factory()->create(['booking_id' => $booking->id]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $booking), [
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
            'dep' => $flight->dep,
            'arr' => $flight->arr,
            'ctot' => '00:30',
            'eta' => '06:00',
        ]);

    $flight->refresh();

    expect($flight->ctot->format('Y-m-d H:i'))->toBe('2026-07-16 00:30')
        ->and($flight->eta->format('Y-m-d H:i'))->toBe('2026-07-16 06:00');
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
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
            'dep' => $event->airportDep->id,
            'arr' => $event->airportArr->id,
            'start' => $event->startEvent->format('H:i'),
            'end' => $event->startEvent->addHour()->format('H:i'),
            'separation' => 10,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    expect(Booking::where('event_id', $event->id)->count())->toBeGreaterThan(0);
});

it('creates bulk slots only within the requested time range on an event past midnight', function (string $start, string $end, array $expectedCtots): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create([
        'startEvent' => '2026-07-15 22:00:00',
        'endEvent' => '2026-07-16 02:00:00',
    ]);

    $this->actingAs($admin)
        ->post(route('admin.bookings.store'), [
            'id' => $event->id,
            'bulk' => true,
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
            'dep' => $event->airportDep->id,
            'arr' => $event->airportArr->id,
            'start' => $start,
            'end' => $end,
            'separation' => 30,
        ])
        ->assertRedirect(route('events.bookings.index', $event));

    $ctots = Flight::whereHas('booking', fn ($query) => $query->where('event_id', $event->id))
        ->orderBy('ctot')
        ->get()
        ->map(fn (Flight $flight): string => $flight->ctot->format('Y-m-d H:i'))
        ->all();

    expect($ctots)->toBe($expectedCtots);
})->with([
    'range before midnight' => ['22:00', '23:30', [
        '2026-07-15 22:00',
        '2026-07-15 22:30',
        '2026-07-15 23:00',
        '2026-07-15 23:30',
    ]],
    'range across midnight' => ['23:30', '00:30', [
        '2026-07-15 23:30',
        '2026-07-16 00:00',
        '2026-07-16 00:30',
    ]],
    'range after midnight' => ['00:30', '01:30', [
        '2026-07-16 00:30',
        '2026-07-16 01:00',
        '2026-07-16 01:30',
    ]],
]);

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
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
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
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
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
            'is_callsign_editable' => true,
            'is_actype_editable' => true,
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

/**
 * Create a booked flight whose after-midnight CTOT is stored a day early.
 */
function bookedFlightWithCtotOnWrongDay(): Flight
{
    $event = Event::factory()->create([
        'startEvent' => '2026-07-15 22:00:00',
        'endEvent' => '2026-07-16 02:00:00',
    ]);

    return Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
        ])->id,
        'ctot' => '2026-07-15 00:30:00',
        'eta' => null,
    ]);
}

/**
 * Build an update payload that leaves the booking and flight unchanged.
 *
 * @return array<string, mixed>
 */
function unchangedBookingPayload(Flight $flight): array
{
    return [
        'is_callsign_editable' => $flight->booking->is_callsign_editable,
        'is_actype_editable' => $flight->booking->is_actype_editable,
        'callsign' => $flight->booking->callsign,
        'acType' => $flight->booking->acType,
        'dep' => $flight->dep,
        'arr' => $flight->arr,
        'route' => $flight->route,
        'oceanicFL' => $flight->oceanicFL,
        'oceanicTrack' => $flight->oceanicTrack,
        'notes' => $flight->notes,
        'ctot' => '00:30',
        'eta' => null,
        'notify_user' => true,
    ];
}

it('leaves a date-only CTOT correction out of the changes sent to the user', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $flight = bookedFlightWithCtotOnWrongDay();
    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $flight->booking), [
            ...unchangedBookingPayload($flight),
            'callsign' => 'NOTIF3',
        ])
        ->assertRedirect(route('events.bookings.index', $flight->booking->event));

    EventFacade::assertDispatched(
        BookingChanged::class,
        fn (BookingChanged $event): bool => $event->changes->pluck('name')->all() === ['callsign']
    );
    expect($flight->refresh()->ctot->format('Y-m-d H:i'))->toBe('2026-07-16 00:30');
});

it('does not notify the user when only the CTOT date is corrected', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $flight = bookedFlightWithCtotOnWrongDay();
    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $flight->booking), unchangedBookingPayload($flight))
        ->assertRedirect(route('events.bookings.index', $flight->booking->event));

    EventFacade::assertNotDispatched(BookingChanged::class);
    expect($flight->refresh()->ctot->format('Y-m-d H:i'))->toBe('2026-07-16 00:30');
});

it('does not notify the user when only an ETA date is corrected', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $flight = bookedFlightWithCtotOnWrongDay();
    $flight->update(['eta' => '2026-07-15 06:00:00']);
    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $flight->booking), [
            ...unchangedBookingPayload($flight),
            'eta' => '06:00',
        ])
        ->assertRedirect(route('events.bookings.index', $flight->booking->event));

    EventFacade::assertNotDispatched(BookingChanged::class);
    expect($flight->refresh()->eta->format('Y-m-d H:i'))->toBe('2026-07-16 06:00');
});

it('does not notify the user when only changes the email cannot show are made', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $flight = bookedFlightWithCtotOnWrongDay();
    $flight->booking->forceFill(['final_information_email_sent_at' => '2026-07-01 12:00:00'])->save();
    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $flight->booking), [
            ...unchangedBookingPayload($flight),
            'is_callsign_editable' => ! $flight->booking->is_callsign_editable,
        ])
        ->assertRedirect(route('events.bookings.index', $flight->booking->event));

    EventFacade::assertNotDispatched(BookingChanged::class);
});

it('notifies the user when a CTOT is set for the first time', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $flight = bookedFlightWithCtotOnWrongDay();
    $flight->update(['ctot' => null]);
    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $flight->booking), unchangedBookingPayload($flight))
        ->assertRedirect(route('events.bookings.index', $flight->booking->event));

    EventFacade::assertDispatched(
        BookingChanged::class,
        fn (BookingChanged $event): bool => $event->changes->pluck('name')->all() === ['ctot']
    );
});

it('notifies the user when the CTOT time changes', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $flight = bookedFlightWithCtotOnWrongDay();
    EventFacade::fake([BookingChanged::class]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.update', $flight->booking), [
            ...unchangedBookingPayload($flight),
            'ctot' => '01:00',
        ])
        ->assertRedirect(route('events.bookings.index', $flight->booking->event));

    EventFacade::assertDispatched(
        BookingChanged::class,
        fn (BookingChanged $event): bool => $event->changes->pluck('name')->all() === ['ctot']
    );
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
