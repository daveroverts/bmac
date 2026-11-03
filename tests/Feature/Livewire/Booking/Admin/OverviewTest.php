<?php

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Events\BookingChanged as BookingChangedEvent;
use App\Events\BookingDeleted as BookingDeletedEvent;
use App\Events\EventBulkEmail as EventBulkEmailEvent;
use App\Livewire\Booking\Admin\Overview;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use App\Notifications\BookingChanged;
use App\Notifications\BookingDeleted;
use App\Notifications\EventBulkEmail;
use Illuminate\Support\Facades\Event as Event_;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

function bookingWithFlight(Event $event, array $bookingAttributes = [], array $flightAttributes = []): Booking
{
    $booking = Booking::factory()->create(['event_id' => $event->id, ...$bookingAttributes]);
    Flight::factory()->create(['booking_id' => $booking->id, ...$flightAttributes]);

    return $booking;
}

function bookedBy(Event $event, array $flightAttributes = []): Booking
{
    return bookingWithFlight($event, [
        'user_id' => User::factory()->create()->id,
        'status' => BookingStatus::BOOKED,
    ], $flightAttributes);
}

beforeEach(function (): void {
    Notification::fake();

    /** @var User $admin */
    $admin = User::factory()->admin()->create();
    $this->admin = $admin;
    $this->event = Event::factory()->create(['event_type_id' => EventType::ONEWAY->value]);
});

it('only lets admins open the page', function (): void {
    /** @var User $pilot */
    $pilot = User::factory()->create();

    $this->actingAs($pilot)
        ->get(route('admin.events.bookings.index', $this->event))
        ->assertForbidden();

    $this->actingAs($this->admin)
        ->get(route('admin.events.bookings.index', $this->event))
        ->assertOk();
});

it('links to the event email page', function (): void {
    $this->actingAs($this->admin)
        ->get(route('admin.events.bookings.index', $this->event))
        ->assertSee(route('admin.events.emails.bulk.create', $this->event));
});

it('blocks non-admins from running a bulk action', function (): void {
    /** @var User $pilot */
    $pilot = User::factory()->create();
    $booking = bookingWithFlight($this->event);

    Livewire::actingAs($pilot)
        ->test(Overview::class, ['event' => $this->event])
        ->assertStatus(404);

    $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
});

it('filters by status and searches by callsign', function (): void {
    bookingWithFlight($this->event, ['callsign' => 'OPEN01', 'status' => BookingStatus::UNASSIGNED]);
    bookedBy($this->event)->update(['callsign' => 'BOOK01']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->assertSee('OPEN01')
        ->assertSee('BOOK01')
        ->set('status', 'booked')
        ->assertDontSee('OPEN01')
        ->assertSee('BOOK01')
        ->set('status', 'all')
        ->set('search', 'OPEN')
        ->assertSee('OPEN01')
        ->assertDontSee('BOOK01');
});

it('toggles every visible row and keeps selections not shown in this view', function (): void {
    $open = bookingWithFlight($this->event, ['status' => BookingStatus::UNASSIGNED]);
    $booked = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$open->id])
        ->set('status', 'booked')
        ->call('toggleVisible')
        ->assertSet('selected', [$open->id, $booked->id])
        ->assertSee('1 selected booking is hidden by the current search or status filter.')
        ->call('toggleVisible')
        ->assertSet('selected', [$open->id]);
});

it('deletes selected bookings and notifies each affected pilot', function (): void {
    $booked = bookedBy($this->event);
    $open = bookingWithFlight($this->event);
    $untouched = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booked->id, $open->id])
        ->assertSee('1 pilot on these bookings.')
        ->call('deleteSelected')
        ->assertSet('selected', []);

    $this->assertDatabaseMissing('bookings', ['id' => $booked->id]);
    $this->assertDatabaseMissing('bookings', ['id' => $open->id]);
    $this->assertDatabaseHas('bookings', ['id' => $untouched->id]);
    Notification::assertSentTo($booked->user, BookingDeleted::class);
    Notification::assertNotSentTo($untouched->user, BookingDeleted::class);
});

it('ignores selected bookings from another event', function (): void {
    $otherBooking = bookedBy(Event::factory()->create());

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$otherBooking->id])
        ->call('deleteSelected');

    $this->assertDatabaseHas('bookings', ['id' => $otherBooking->id]);
    Notification::assertNothingSent();
});

it('refuses bulk changes after the event has ended', function (): void {
    $event = Event::factory()->expired()->create();
    $booking = bookedBy($event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $event])
        ->assertSee('This event has ended')
        ->set('selected', [$booking->id])
        ->call('deleteSelected')
        ->set('enabledFields.acType', true)
        ->set('acType', 'B738')
        ->call('applyChanges')
        ->call('openPanel', 'edit')
        ->assertSet('panel', null)
        ->call('openPanel', 'delete')
        ->assertSet('panel', null);

    $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    expect($booking->fresh()->acType)->not->toBe('B738');
    Notification::assertNothingSent();
});

it('applies only switched-on fields, shifts times and notifies pilots', function (): void {
    $this->event->update(['uses_times' => true]);
    $ctot = now()->addDays(10)->setTime(18, 0);
    $booking = bookedBy($this->event, ['ctot' => $ctot, 'eta' => $ctot->copy()->addHour(), 'route' => 'KEEP']);
    $booking->update(['acType' => 'A320', 'final_information_email_sent_at' => now()]);

    $open = bookingWithFlight($this->event, ['acType' => 'A321']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id, $open->id])
        ->set('enabledFields.acType', true)
        ->set('acType', 'b738')
        ->set('enabledFields.route', false)
        ->set('route', 'IGNORED')
        ->set('enabledFields.shiftMinutes', true)
        ->set('shiftMinutes', '15')
        ->call('applyChanges')
        ->assertHasNoErrors()
        ->assertSet('selected', []);

    $booking->refresh();
    $flight = $booking->flights->first();

    expect($booking->acType)->toBe('B738')
        ->and($booking->final_information_email_sent_at)->toBeNull()
        ->and($open->fresh()->acType)->toBe('B738')
        ->and($flight->route)->toBe('KEEP')
        ->and($flight->ctot->format('H:i'))->toBe('18:15')
        ->and($flight->eta->format('H:i'))->toBe('19:15');

    Notification::assertSentTo($booking->user, BookingChanged::class);
});

it('does not notify pilots when notify is off', function (): void {
    $booking = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->set('enabledFields.route', true)
        ->set('route', 'NEW ROUTE')
        ->set('notifyUsers', false)
        ->call('applyChanges');

    expect($booking->flights()->first()->route)->toBe('NEW ROUTE');
    Notification::assertNothingSent();
});

it('updates every flight when shifting a multiflights booking but ignores route', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::MULTIFLIGHTS->value, 'uses_times' => true]);
    $ctot = now()->addDays(10)->setTime(10, 0);
    $booking = bookedBy($event, ['ctot' => $ctot, 'route' => 'FIRST']);
    Flight::factory()->create(['booking_id' => $booking->id, 'order_by' => 2, 'ctot' => $ctot->copy()->addHours(2), 'route' => 'SECOND']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $event])
        ->set('selected', [$booking->id])
        ->set('enabledFields.route', true)
        ->set('route', 'OVERWRITE')
        ->set('enabledFields.shiftMinutes', true)
        ->set('shiftDirection', 'earlier')
        ->set('shiftMinutes', '30')
        ->call('applyChanges');

    $flights = $booking->flights()->orderBy('ctot')->get();

    expect($flights->pluck('route')->all())->toBe(['FIRST', 'SECOND'])
        ->and($flights->map(fn (Flight $flight): string => $flight->ctot->format('H:i'))->all())->toBe(['09:30', '11:30']);
});

it('requires at least one field and a valid aircraft code', function (): void {
    $booking = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('applyChanges')
        ->assertHasErrors('enabledFields')
        ->set('enabledFields.acType', true)
        ->set('acType', 'TOOLONG')
        ->call('applyChanges')
        ->assertHasErrors('acType')
        ->assertSet('selected', [$booking->id]);
});

it('emails each selected pilot once with an escaped message', function (): void {
    $pilot = User::factory()->create();
    $first = bookingWithFlight($this->event, ['user_id' => $pilot->id, 'status' => BookingStatus::BOOKED]);
    $second = bookingWithFlight($this->event, ['user_id' => $pilot->id, 'status' => BookingStatus::BOOKED]);
    $notSelected = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$first->id, $second->id])
        ->set('emailSubject', 'Gate change')
        ->set('emailMessage', "Hello <b>there</b>\nBye")
        ->call('sendEmail')
        ->assertHasNoErrors();

    Notification::assertSentToTimes($pilot, EventBulkEmail::class, 1);
    Notification::assertSentTo($pilot, EventBulkEmail::class, fn (EventBulkEmail $notification): bool => str_contains($notification->content, '&lt;b&gt;there&lt;/b&gt;<br />'));
    Notification::assertNotSentTo($notSelected->user, EventBulkEmail::class);
});

it('requires a subject and message before emailing', function (): void {
    $booking = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('sendEmail')
        ->assertHasErrors(['emailSubject', 'emailMessage']);

    Notification::assertNothingSent();
});

it('sends one deletion email to a pilot with several selected bookings', function (): void {
    $pilot = User::factory()->create();
    $first = bookingWithFlight($this->event, ['user_id' => $pilot->id, 'status' => BookingStatus::BOOKED]);
    $second = bookingWithFlight($this->event, ['user_id' => $pilot->id, 'status' => BookingStatus::BOOKED]);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$first->id, $second->id])
        ->call('deleteSelected');

    Notification::assertSentToTimes($pilot, BookingDeleted::class, 1);
});

it('does not email when none of the selected bookings have a pilot', function (): void {
    $open = bookingWithFlight($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$open->id])
        ->set('emailSubject', 'Subject')
        ->set('emailMessage', 'Message')
        ->call('sendEmail')
        ->assertHasErrors('emailSubject');

    Notification::assertNothingSent();
});

it('does nothing when bulk changes target no bookings of this event', function (): void {
    $otherBooking = bookedBy(Event::factory()->create());

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$otherBooking->id])
        ->set('enabledFields.acType', true)
        ->set('acType', 'B738')
        ->call('applyChanges');

    expect($otherBooking->fresh()->acType)->not->toBe('B738');
    Notification::assertNothingSent();
});

it('clears a switched-on field left empty and keeps fields that are off', function (): void {
    $booking = bookedBy($this->event, ['route' => 'OLD ROUTE', 'notes' => 'KEEP']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->set('enabledFields.route', true)
        ->call('applyChanges')
        ->assertHasNoErrors();

    $flight = $booking->flights()->first();

    expect($flight->route)->toBeNull()
        ->and($flight->notes)->toBe('KEEP');
});

it('shows what the selected bookings hold now in the edit panel', function (): void {
    $first = bookingWithFlight($this->event, ['acType' => 'B738', 'is_callsign_editable' => true, 'is_actype_editable' => false], ['route' => 'EHAM DCT KJFK', 'notes' => 'GATE 4', 'oceanicFL' => null]);
    $second = bookingWithFlight($this->event, ['acType' => 'A320', 'is_callsign_editable' => false, 'is_actype_editable' => false], ['route' => 'EHAM DCT KJFK', 'notes' => 'GATE 9', 'oceanicFL' => '']);
    $third = bookingWithFlight($this->event, ['acType' => 'A320', 'is_callsign_editable' => true, 'is_actype_editable' => false], ['route' => 'EHAM DCT KJFK', 'notes' => null, 'oceanicFL' => null]);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$first->id, $second->id, $third->id])
        ->call('openPanel', 'edit')
        ->assertSeeHtml('id="bulk-now-route" class="form-text mt-1 text-break">Now: EHAM DCT KJFK<')
        ->assertSeeHtml('id="bulk-now-acType" class="form-text mt-1 text-break">Now: 2 different values<')
        ->assertSeeHtml('id="bulk-now-notes" class="form-text mt-1 text-break">Now: 2 different values, empty on 1 booking<')
        ->assertSeeHtml('id="bulk-now-oceanicFL" class="form-text mt-1 text-break">Now: empty<')
        ->assertSeeHtml('id="bulk-now-isCallsignEditable" class="form-text mt-1 text-break">Now: yes on 2, no on 1<')
        ->assertSeeHtml('id="bulk-now-isActypeEditable" class="form-text mt-1 text-break">Now: no<');
});

it('renders a clear warning only for switched-on fields that hold a value', function (): void {
    $first = bookingWithFlight($this->event, [], ['route' => 'EHAM DCT KJFK', 'notes' => null]);
    $second = bookingWithFlight($this->event, [], ['route' => null, 'notes' => null]);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$first->id, $second->id])
        ->call('openPanel', 'edit')
        ->assertSee('Now: EHAM DCT KJFK, empty on 1 booking')
        ->assertDontSee('Left empty, this clears')
        ->set('enabledFields.notes', true)
        ->assertDontSee('Left empty, this clears')
        ->set('enabledFields.route', true)
        ->assertSeeHtml('id="bulk-clear-route"')
        ->assertSee('Left empty, this clears the value on 1 booking.');
});

it('hides and ignores the time shift for an event without times', function (): void {
    $ctot = now()->addDays(10)->setTime(18, 0);
    $booking = bookedBy($this->event, ['ctot' => $ctot]);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'edit')
        ->assertDontSee('Shift CTOT and ETA')
        ->set('enabledFields.shiftMinutes', true)
        ->set('shiftMinutes', '15')
        ->call('applyChanges')
        ->assertHasErrors('enabledFields');

    expect($booking->flights()->first()->ctot->format('H:i'))->toBe('18:00');
});

it('updates editable flags without emailing pilots or resetting final information', function (): void {
    $booking = bookedBy($this->event);
    $booking->update(['is_callsign_editable' => false, 'final_information_email_sent_at' => now()]);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->set('enabledFields.isCallsignEditable', true)
        ->set('isCallsignEditable', '1')
        ->call('applyChanges')
        ->assertHasNoErrors();

    $booking->refresh();

    expect($booking->is_callsign_editable)->toBeTrue()
        ->and($booking->final_information_email_sent_at)->not->toBeNull();
    Notification::assertNothingSent();
});

it('requires a yes or no choice for a switched-on editable flag', function (): void {
    $booking = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->set('enabledFields.isActypeEditable', true)
        ->call('applyChanges')
        ->assertHasErrors('isActypeEditable');
});

it('paginates rows ordered by earliest flight time', function (): void {
    $base = now()->addDays(10)->setTime(10, 0);

    foreach (range(1, 26) as $number) {
        $booking = bookingWithFlight($this->event, [], ['ctot' => $base->copy()->addMinutes(60 - $number), 'eta' => null]);
        $booking->update(['callsign' => 'SLOT' . $number]);
    }

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->assertSeeInOrder(['SLOT26', 'SLOT25'])
        ->assertDontSee('SLOT1<')
        ->call('nextPage')
        ->assertSee('SLOT1')
        ->assertDontSee('SLOT26');
});

it('falls back to the default page size for an invalid per page value', function (): void {
    foreach (range(1, 26) as $number) {
        bookingWithFlight($this->event);
    }

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('perPage', 13);

    expect($component->instance()->bookings->perPage())->toBe(25);
});

it('offers to select every matching booking across pages and respects the filter', function (): void {
    $open = collect(range(1, 26))->map(fn (): Booking => bookingWithFlight($this->event, ['status' => BookingStatus::UNASSIGNED]));
    $booked = bookedBy($this->event);

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('status', 'open')
        ->assertDontSee('Select all 26 matching bookings')
        ->call('toggleVisible')
        ->assertSee('All 25 bookings on this page are selected.')
        ->assertSee('Select all 26 matching bookings')
        ->call('selectAllMatching')
        ->assertSee('All 26 matching bookings are selected.')
        ->assertDontSee('Select all 26 matching bookings');

    expect($component->get('selected'))->toHaveCount(26)
        ->toEqualCanonicalizing($open->pluck('id')->all())
        ->not->toContain($booked->id);
});

it('selects all bookings matching a search and keeps earlier selections', function (): void {
    $kept = bookingWithFlight($this->event, ['callsign' => 'KLM001']);
    $matching = collect(range(1, 25))->map(fn (int $number): Booking => bookingWithFlight($this->event, ['callsign' => sprintf('DLH%03d', $number)]));
    $otherEvent = Event::factory()->create(['event_type_id' => EventType::ONEWAY->value]);
    bookingWithFlight($otherEvent, ['callsign' => 'DLH999']);

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$kept->id])
        ->set('search', 'DLH')
        ->set('perPage', 25)
        ->call('toggleVisible')
        ->assertSee('Keep only 25 matching selected bookings')
        ->call('selectAllMatching');

    expect($component->get('selected'))->toEqualCanonicalizing([$kept->id, ...$matching->pluck('id')->all()]);
});

it('ignores bookings from another event in a shift-clicked range', function (): void {
    $booking = bookingWithFlight($this->event);
    $foreign = bookingWithFlight(Event::factory()->create());

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->call('selectRange', [$booking->id, $foreign->id], true)
        ->assertSet('selected', [$booking->id]);
});

it('selects and clears a shift-clicked range of rows', function (): void {
    [$first, $second, $third] = collect(range(1, 3))->map(fn (): Booking => bookingWithFlight($this->event))->all();

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$first->id])
        ->call('selectRange', [(string) $second->id, (string) $third->id], true);

    expect($component->get('selected'))->toEqualCanonicalizing([$first->id, $second->id, $third->id]);

    $component->call('selectRange', [$first->id, $second->id], false)
        ->assertSet('selected', [$third->id]);
});

it('closes the panel when a range clear empties the selection', function (): void {
    $booking = bookingWithFlight($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'edit')
        ->call('selectRange', [$booking->id], false)
        ->assertSet('panel', null);
});

it('toggles only the current page and acts on selections from other pages', function (): void {
    $bookings = collect(range(1, 26))->map(fn (): Booking => bookingWithFlight($this->event));

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->call('toggleVisible');

    expect($component->get('selected'))->toHaveCount(25);

    $component->call('nextPage')
        ->assertSee('25 matching selected bookings are on other pages.')
        ->call('toggleVisible');

    expect($component->get('selected'))->toHaveCount(26);

    $component->call('deleteSelected');

    expect(Booking::query()->whereKey($bookings->pluck('id'))->count())->toBe(0);
});

it('shows current counts in the delete panel after the selection changes', function (): void {
    $booked = bookedBy($this->event);
    $open = bookingWithFlight($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booked->id])
        ->call('openPanel', 'delete')
        ->assertSee('Delete 1 booking?')
        ->set('selected', [$booked->id, $open->id])
        ->assertSee('Delete 2 bookings?')
        ->assertSee('1 pilot gets a &quot;booking deleted&quot; email.', false);
});

it('discards the edit draft on close but keeps a written email', function (): void {
    $booking = bookedBy($this->event, ['notes' => 'KEEP']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'edit')
        ->set('enabledFields.notes', true)
        ->set('acType', 'B738')
        ->call('closePanel')
        ->assertSet('enabledFields', [])
        ->assertSet('acType', '')
        ->call('openPanel', 'email')
        ->set('emailSubject', 'Gate change')
        ->set('emailTestMode', true)
        ->call('closePanel')
        ->assertSet('emailSubject', 'Gate change')
        ->assertSet('emailTestMode', false)
        ->call('openPanel', 'edit')
        ->call('applyChanges')
        ->assertHasErrors('enabledFields');

    expect($booking->flights()->first()->notes)->toBe('KEEP');
});

it('disables saving until a field is switched on', function (): void {
    $booking = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'edit')
        ->assertSeeHtml('wire:target="applyChanges" disabled')
        ->set('enabledFields.route', true)
        ->assertDontSeeHtml('wire:target="applyChanges" disabled');
});

it('names the admin address and retitles the panel in email test mode', function (): void {
    $booking = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'email')
        ->assertSee('Test mode: send only to me ('.$this->admin->email.')')
        ->assertSee('Email 1 pilot')
        ->set('emailTestMode', true)
        ->assertSee('Send a test email to yourself')
        ->assertDontSee('Email 1 pilot');
});

it('closes the panel when the selection becomes empty', function (): void {
    $booked = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->assertSee('No bookings selected')
        ->set('selected', [$booked->id])
        ->call('openPanel', 'edit')
        ->set('selected', [])
        ->assertSet('panel', null)
        ->assertSee('No bookings selected');
});

it('sends a test email only to the admin and shows feedback inside the open modal', function (): void {
    $booking = bookedBy($this->event);

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'email')
        ->set('emailSubject', 'Subject')
        ->set('emailMessage', 'Message')
        ->set('emailTestMode', true)
        ->call('sendEmail')
        ->assertHasNoErrors()
        ->assertSet('selected', [$booking->id])
        ->assertSet('panel', 'email')
        ->assertSee('Email has been sent to yourself');

    expect($component->effects['xjs'] ?? [])->toBeEmpty();

    Notification::assertSentTo($this->admin, EventBulkEmail::class);
    Notification::assertNotSentTo($booking->user, EventBulkEmail::class);
});

it('rejects a flight level longer than three digits', function (): void {
    $booking = bookedBy($this->event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->set('enabledFields.oceanicFL', true)
        ->set('oceanicFL', '3500')
        ->call('applyChanges')
        ->assertHasErrors('oceanicFL');
});

it('shows a dash in the change email for an empty value', function (): void {
    $booking = bookedBy($this->event);

    $mail = (new BookingChanged($booking, collect([['name' => 'route', 'old' => null, 'new' => 'NEW ROUTE']])))->toMail($booking->user);

    expect((string) $mail->render())->toContain('NEW ROUTE')->toContain('(was -)');
});

it('can still email pilots after the event has ended', function (): void {
    $event = Event::factory()->expired()->create();
    $booking = bookedBy($event);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'email')
        ->assertSet('panel', 'email')
        ->set('emailSubject', 'Thank you')
        ->set('emailMessage', 'Thanks for flying')
        ->call('sendEmail')
        ->assertHasNoErrors();

    Notification::assertSentTo($booking->user, EventBulkEmail::class);
});

it('saves changes, clears the selection and warns when a notification fails', function (): void {
    $failing = bookedBy($this->event);
    $next = bookedBy($this->event);

    Event_::listen(BookingChangedEvent::class, function (BookingChangedEvent $event) use ($failing): void {
        throw_if($event->booking->is($failing), RuntimeException::class, 'Queue down');
    });

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$failing->id, $next->id])
        ->set('enabledFields.acType', true)
        ->set('acType', 'B738')
        ->call('applyChanges')
        ->assertSet('selected', []);

    expect($component->effects['xjs'][0]['expression'] ?? '')->toContain('1 pilot could not be notified')
        ->and($failing->fresh()->acType)->toBe('B738');
    Notification::assertSentTo($next->user, BookingChanged::class);
});

it('deletes every booking and warns when a deletion email fails', function (): void {
    $failing = bookedBy($this->event);
    $next = bookedBy($this->event);

    Event_::listen(BookingDeletedEvent::class, function (BookingDeletedEvent $event) use ($failing): void {
        throw_if($event->user->is($failing->user), RuntimeException::class, 'Queue down');
    });

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$failing->id, $next->id])
        ->call('deleteSelected')
        ->assertSet('selected', []);

    expect($component->effects['xjs'][0]['expression'] ?? '')->toContain('1 pilot could not be notified');
    $this->assertDatabaseMissing('bookings', ['id' => $failing->id]);
    Notification::assertSentTo($next->user, BookingDeleted::class);
});

it('names the slots in the deletion email for a pilot losing several', function (): void {
    $pilot = User::factory()->create();
    $first = bookingWithFlight($this->event, ['user_id' => $pilot->id, 'status' => BookingStatus::BOOKED, 'callsign' => 'ONE111']);
    $second = bookingWithFlight($this->event, ['user_id' => $pilot->id, 'status' => BookingStatus::BOOKED, 'callsign' => 'TWO222']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$first->id, $second->id])
        ->call('deleteSelected');

    Notification::assertSentTo($pilot, BookingDeleted::class, function (BookingDeleted $notification) use ($pilot): bool {
        $line = implode(' ', $notification->toMail($pilot)->introLines);

        return str_contains($line, 'ONE111') && str_contains($line, 'TWO222');
    });
});

it('treats search wildcards as literal text', function (): void {
    bookedBy($this->event)->update(['callsign' => 'ABC123']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->assertSee('ABC123')
        ->set('search', '%')
        ->assertDontSee('ABC123')
        ->set('search', 'A_C')
        ->assertDontSee('ABC123')
        ->set('search', 'ABC')
        ->assertSee('ABC123');
});

it('finds a pilot by full name', function (): void {
    $booking = bookedBy($this->event);
    $booking->update(['callsign' => 'ABC123']);
    $booking->user->update(['name_first' => 'Johnathan', 'name_last' => 'Doeling']);

    bookedBy($this->event)->update(['callsign' => 'XYZ999']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('search', 'Johnathan Doeling')
        ->assertSee('ABC123')
        ->assertDontSee('XYZ999');
});

it('labels each flight in the change email for multiflights', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::MULTIFLIGHTS->value, 'uses_times' => true]);
    $ctot = now()->addDays(10)->setTime(10, 0);
    $booking = bookedBy($event, ['ctot' => $ctot]);
    Flight::factory()->create(['booking_id' => $booking->id, 'order_by' => 2, 'ctot' => $ctot->copy()->addHours(2)]);
    $booking->flights()->orderBy('id')->first()->update(['order_by' => 1]);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $event])
        ->set('selected', [$booking->id])
        ->set('enabledFields.shiftMinutes', true)
        ->set('shiftMinutes', '5')
        ->call('applyChanges');

    Notification::assertSentTo($booking->user, BookingChanged::class, function (BookingChanged $notification) use ($booking): bool {
        $html = (string) $notification->toMail($booking->user)->render();

        return str_contains($html, 'Flight #1 CTOT:') && str_contains($html, 'Flight #2 CTOT:');
    });
});

it('shows a dash for a cleared value in the change email', function (): void {
    $booking = bookedBy($this->event);

    $html = (string) (new BookingChanged($booking, collect([['name' => 'route', 'old' => 'OLD ROUTE', 'new' => null]])))->toMail($booking->user)->render();

    expect($html)->toMatch('/<strong[^>]*>-<\/strong>/')->not->toContain('**-**');
});

it('tells admins when an email includes reserved bookings', function (): void {
    $reserved = bookingWithFlight($this->event, ['user_id' => User::factory()->create()->id, 'status' => BookingStatus::RESERVED]);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$reserved->id])
        ->call('openPanel', 'email')
        ->assertSee('Includes 1 reserved booking.');
});

it('leaves out missing callsigns in the deletion email', function (): void {
    $pilot = User::factory()->create();
    $notification = new BookingDeleted($this->event, ['-', '-']);

    expect(implode(' ', $notification->toMail($pilot)->introLines))
        ->toContain('Your 2 bookings')
        ->not->toContain('(-');
});

it('clears the selection and says so when selected bookings no longer exist', function (): void {
    $booking = bookedBy($this->event);

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id]);

    $booking->delete();

    $component->call('deleteSelected')->assertSet('selected', []);

    expect($component->effects['xjs'][0]['expression'] ?? '')->toContain('no longer exist');
});

it('keeps the selection and reports it when a bulk email cannot be sent', function (): void {
    $booking = bookedBy($this->event);

    Event_::listen(EventBulkEmailEvent::class, function (): void {
        throw new RuntimeException('Queue down');
    });

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'email')
        ->set('emailSubject', 'Subject')
        ->set('emailMessage', 'Message')
        ->call('sendEmail')
        ->assertSet('selected', [$booking->id])
        ->assertSet('panel', 'email')
        ->assertSee('check before retrying');

    expect($component->effects['xjs'] ?? [])->toBeEmpty();
});


it('preserves selection and distinguishes search and status exclusions', function (): void {
    $open = bookingWithFlight($this->event, ['callsign' => 'KLM001', 'status' => BookingStatus::UNASSIGNED]);
    $booked = bookedBy($this->event);
    $booked->update(['callsign' => 'DLH001']);

    $selected = [$open->id, $booked->id];

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', $selected)
        ->set('search', 'KLM')
        ->assertSet('selected', $selected)
        ->assertViewHas('selectedMatchingCount', 1)
        ->assertViewHas('hiddenByFilterCount', 1)
        ->assertViewHas('otherPageCount', 0)
        ->assertSee('Actions include these hidden selections.')
        ->assertSee('Keep only 1 matching selected booking')
        ->assertDontSee('Keep only 1 matching selected bookings')
        ->set('search', '')
        ->set('status', 'booked')
        ->assertSet('selected', $selected)
        ->assertViewHas('selectedMatchingCount', 1)
        ->assertViewHas('hiddenByFilterCount', 1)
        ->set('status', 'all')
        ->assertSet('selected', $selected)
        ->assertViewHas('selectedMatchingCount', 2)
        ->assertViewHas('hiddenByFilterCount', 0)
        ->assertDontSee('Actions include these hidden selections.');
});

it('counts matching selections across pages separately from filtered selections', function (): void {
    $matching = Booking::factory()->count(26)->create(['event_id' => $this->event->id, 'callsign' => 'KLM001']);
    $hidden = bookingWithFlight($this->event, ['callsign' => 'DLH001']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$matching->first()->id, $matching->last()->id, $hidden->id])
        ->set('search', 'KLM')
        ->assertViewHas('selectedMatchingCount', 2)
        ->assertViewHas('hiddenByFilterCount', 1)
        ->assertViewHas('otherPageCount', 1)
        ->call('nextPage')
        ->assertViewHas('selectedMatchingCount', 2)
        ->assertViewHas('hiddenByFilterCount', 1)
        ->assertViewHas('otherPageCount', 1)
        ->assertSee('Selected: 2 matching; 3 total.')
        ->call('selectAllMatching')
        ->assertSee('Selected: 26 matching; 27 total.')
        ->assertDontSee('All 26 matching bookings are selected.');
});

it('keeps only existing matching selections across pages and excludes other events', function (): void {
    $matching = Booking::factory()->count(26)->create(['event_id' => $this->event->id, 'callsign' => 'KLM001', 'status' => BookingStatus::UNASSIGNED]);
    $wrongSearch = bookingWithFlight($this->event, ['callsign' => 'DLH001', 'status' => BookingStatus::UNASSIGNED]);
    $wrongStatus = bookingWithFlight($this->event, ['callsign' => 'KLM002', 'status' => BookingStatus::RESERVED]);
    $foreign = bookingWithFlight(Event::factory()->create(), ['callsign' => 'KLM003', 'status' => BookingStatus::UNASSIGNED]);
    $kept = [$matching->first()->id, $matching->last()->id];

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [...$kept, $wrongSearch->id, $wrongStatus->id, $foreign->id])
        ->set('search', 'KLM')
        ->set('status', 'open')
        ->assertViewHas('selectedCount', 4)
        ->assertViewHas('selectedMatchingCount', 2)
        ->assertViewHas('hiddenByFilterCount', 2)
        ->assertSee('Keep only 2 matching selected bookings')
        ->call('selectOnlyMatching')
        ->assertViewHas('hiddenByFilterCount', 0);

    expect($component->get('selected'))->toEqualCanonicalizing($kept);
});

it('clears the selection and edit draft when no selected bookings match', function (): void {
    $booking = bookingWithFlight($this->event, ['callsign' => 'KLM001']);
    bookingWithFlight($this->event, ['callsign' => 'DLH001']);

    Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'edit')
        ->set('enabledFields.acType', true)
        ->set('acType', 'A320')
        ->set('search', 'DLH')
        ->assertViewHas('selectedMatchingCount', 0)
        ->assertSee('Clear selection')
        ->assertDontSee('Keep only 0')
        ->call('selectOnlyMatching')
        ->assertSet('selected', [])
        ->assertSet('panel', null)
        ->assertSet('enabledFields', [])
        ->assertSet('acType', '')
        ->assertSee('No bookings selected');
});

it('retries only remaining pilots after a partial email failure', function (): void {

    $first = bookingWithFlight($this->event, ['user_id' => User::factory()->create(['id' => 1000001])->id, 'status' => BookingStatus::BOOKED]);
    $samePilot = bookingWithFlight($this->event, ['user_id' => $first->user_id, 'status' => BookingStatus::BOOKED]);
    $failing = bookingWithFlight($this->event, ['user_id' => User::factory()->create(['id' => 1000002])->id, 'status' => BookingStatus::BOOKED]);
    $last = bookingWithFlight($this->event, ['user_id' => User::factory()->create(['id' => 1000003])->id, 'status' => BookingStatus::BOOKED]);
    $submitted = [];
    $failOnce = true;

    Notification::shouldReceive('send')->andReturnUsing(function ($users) use ($failing, &$submitted, &$failOnce): void {
        foreach ($users as $user) {
            if ($user->id === $failing->user_id && $failOnce) {
                $failOnce = false;
                throw new RuntimeException('Mail unavailable');
            }

            $submitted[] = $user->id;
        }
    });

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$first->id, $samePilot->id, $failing->id, $last->id])
        ->call('openPanel', 'email')
        ->set('emailSubject', 'Subject')
        ->set('emailMessage', 'Message')
        ->call('sendEmail')
        ->assertSet('selected', [$failing->id, $last->id])
        ->assertSet('panel', 'email')
        ->assertSet('emailSubject', 'Subject')
        ->assertSet('emailMessage', 'Message')
        ->assertSee('check before retrying');

    expect($submitted)->toBe([$first->user_id]);

    $component->call('sendEmail')->assertSet('selected', [])->assertSet('panel', null);

    expect($submitted)->toBe([$first->user_id, $failing->user_id, $last->user_id]);
});

it('keeps test email failures inside the modal and preserves the draft', function (): void {
    $booking = bookedBy($this->event);
    Event_::listen(EventBulkEmailEvent::class, function (): void {
        throw new RuntimeException('Queue down');
    });

    $component = Livewire::actingAs($this->admin)
        ->test(Overview::class, ['event' => $this->event])
        ->set('selected', [$booking->id])
        ->call('openPanel', 'email')
        ->set('emailSubject', 'Subject')
        ->set('emailMessage', 'Message')
        ->set('emailTestMode', true)
        ->call('sendEmail')
        ->assertSet('selected', [$booking->id])
        ->assertSet('panel', 'email')
        ->assertSet('emailSubject', 'Subject')
        ->assertSet('emailMessage', 'Message')
        ->assertSee('The test email could not be sent.');

    expect($component->effects['xjs'] ?? [])->toBeEmpty();

    $component->call('closePanel')->call('openPanel', 'email')
        ->assertDontSee('The test email could not be sent.');
});
