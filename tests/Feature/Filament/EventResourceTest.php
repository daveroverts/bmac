<?php

use App\Enums\BookingStatus;
use App\Enums\EventType;
use App\Events\BookingDeleted;
use App\Events\EventBulkEmail;
use App\Events\EventFinalInformation;
use App\Filament\Resources\Events\Pages\CreateEvent;
use App\Filament\Resources\Events\Pages\EditEvent;
use App\Filament\Resources\Events\Pages\ImportBookings;
use App\Filament\Resources\Events\Pages\ListEvents;
use App\Filament\Resources\Events\Pages\ManageEventBookings;
use App\Filament\Resources\Events\Pages\ViewEvent;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use App\Notifications\BookingChanged;
use Filament\Actions\DeleteAction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Event as EventFacade;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

function filamentSlot(Event $event, array $booking = [], array $flight = []): Booking
{
    $slot = Booking::factory()->create(['event_id' => $event->id, ...$booking]);

    Flight::create([
        'booking_id' => $slot->id,
        'order_by' => 1,
        'dep' => $event->dep,
        'arr' => $event->arr,
        'ctot' => $event->startEvent,
        ...$flight,
    ]);

    return $slot->refresh();
}

function filamentBookedSlot(Event $event, array $flight = []): Booking
{
    return filamentSlot($event, [
        'user_id' => User::factory()->create()->id,
        'status' => BookingStatus::BOOKED,
        'callsign' => 'KLM' . random_int(100, 999),
    ], $flight);
}

beforeEach(function (): void {
    /** @var TestCase $this */
    Notification::fake();

    $this->admin = User::factory()->admin()->create();
    $this->actingAs($this->admin);
    $this->event = Event::factory()->create(['event_type_id' => EventType::ONEWAY->value, 'uses_times' => true]);
});

it('forbids non-admins from the event pages', function (): void {
    /** @var TestCase $this */
    $this->actingAs(User::factory()->create())
        ->get('/filament/events')
        ->assertForbidden();
});

it('lists events', function (): void {
    Livewire::test(ListEvents::class)
        ->assertOk()
        ->assertCanSeeTableRecords([$this->event]);
});

it('creates an event', function (): void {
    [$dep, $arr] = Airport::factory()->count(2)->create();

    Livewire::test(CreateEvent::class)
        ->fillForm([
            'name' => 'Filament Fly-In',
            'event_type_id' => EventType::FLYIN->value,
            'dep' => $dep->id,
            'arr' => $arr->id,
            'startEvent' => '2030-06-01 14:00',
            'endEvent' => '2030-06-01 17:00',
            'startBooking' => '2030-05-01 12:00',
            'endBooking' => '2030-06-01 12:00',
            'is_online' => true,
            'description' => '<p>Hello</p>',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('events', ['name' => 'Filament Fly-In', 'dep' => $dep->id, 'is_online' => true]);
});

it('rejects an event that ends before it starts', function (): void {
    Livewire::test(EditEvent::class, ['record' => $this->event->getRouteKey()])
        ->fillForm(['endEvent' => $this->event->startEvent->copy()->subHour()->format('Y-m-d H:i')])
        ->call('save')
        ->assertHasFormErrors(['endEvent']);
});

it('hides delete for events that have started', function (): void {
    $event = Event::factory()->expired()->create();

    Livewire::test(EditEvent::class, ['record' => $event->getRouteKey()])
        ->assertActionHidden(DeleteAction::class);
});

it('lists, filters and searches the bookings of an event', function (): void {
    $open = filamentSlot($this->event);
    $booked = filamentBookedSlot($this->event);
    $other = filamentSlot(Event::factory()->create());

    Livewire::test(ManageEventBookings::class, ['record' => $this->event->getRouteKey()])
        ->assertCanSeeTableRecords([$open, $booked])
        ->assertCanNotSeeTableRecords([$other])
        ->filterTable('status', BookingStatus::BOOKED->value)
        ->assertCanSeeTableRecords([$booked])
        ->assertCanNotSeeTableRecords([$open])
        ->resetTableFilters()
        ->searchTable($booked->user->name_last)
        ->assertCanSeeTableRecords([$booked])
        ->assertCanNotSeeTableRecords([$open]);
});

it('adds timeslots', function (): void {
    $event = Event::factory()->create([
        'startEvent' => '2030-06-01 14:00:00',
        'endEvent' => '2030-06-01 17:00:00',
    ]);

    Livewire::test(ManageEventBookings::class, ['record' => $event->getRouteKey()])
        ->callAction('addTimeslots', [
            'dep' => $event->dep,
            'arr' => $event->arr,
            'start' => '14:00',
            'end' => '15:00',
            'separation' => 20,
        ])
        ->assertHasNoActionErrors();

    expect($event->bookings()->count())->toBe(4)
        ->and(Flight::whereIn('booking_id', $event->bookings()->select('id'))->orderBy('ctot')->first()->ctot->format('Y-m-d H:i'))->toBe('2030-06-01 14:00');
});

it('does not offer manual slots for multi flights events', function (): void {
    $event = Event::factory()->create(['event_type_id' => EventType::MULTIFLIGHTS->value]);

    Livewire::test(ManageEventBookings::class, ['record' => $event->getRouteKey()])
        ->assertActionHidden('addTimeslots')
        ->assertActionHidden('addSlot');
});

it('adds a single slot', function (): void {
    Livewire::test(ManageEventBookings::class, ['record' => $this->event->getRouteKey()])
        ->callAction('addSlot', [
            'callsign' => 'klm123',
            'acType' => 'b738',
            'dep' => $this->event->dep,
            'arr' => $this->event->arr,
            'ctot' => $this->event->startEvent->format('H:i'),
        ])
        ->assertHasNoActionErrors();

    $this->assertDatabaseHas('bookings', ['event_id' => $this->event->id, 'callsign' => 'KLM123', 'acType' => 'B738']);
});

it('edits a booking and notifies the pilot', function (): void {
    $booking = filamentBookedSlot($this->event);

    Livewire::test(ManageEventBookings::class, ['record' => $this->event->getRouteKey()])
        ->callAction(TestAction::make('edit')->table($booking), [
            'route' => 'DCT',
            'notify_user' => true,
            'message' => 'New route',
        ])
        ->assertHasNoActionErrors();

    expect($booking->flights()->first()->route)->toBe('DCT');
    Notification::assertSentTo($booking->user, BookingChanged::class);
});

it('deletes a booking and tells the pilot', function (): void {
    EventFacade::fake([BookingDeleted::class]);
    $booking = filamentBookedSlot($this->event);

    Livewire::test(ManageEventBookings::class, ['record' => $this->event->getRouteKey()])
        ->callAction(TestAction::make('delete')->table($booking));

    $this->assertModelMissing($booking);
    EventFacade::assertDispatched(BookingDeleted::class, fn (BookingDeleted $event): bool => $event->user->is($booking->user));
});

it('bulk edits only the switched-on fields', function (): void {
    $first = filamentBookedSlot($this->event, ['route' => 'OLD', 'notes' => 'keep']);
    $second = filamentSlot($this->event, [], ['route' => 'OLD', 'notes' => 'keep']);

    Livewire::test(ManageEventBookings::class, ['record' => $this->event->getRouteKey()])
        ->selectTableRecords([$first->id, $second->id])
        ->callAction(TestAction::make('bulkEdit')->table()->bulk(), [
            'change' => ['route' => true, 'timeShift' => true],
            'route' => 'NEW',
            'notes' => 'ignored',
            'shiftDirection' => 'later',
            'shiftMinutes' => 15,
            'notifyUsers' => true,
        ])
        ->assertHasNoActionErrors();

    $flight = $first->flights()->first();
    expect($flight->route)->toBe('NEW')
        ->and($flight->notes)->toBe('keep')
        ->and($flight->ctot->equalTo($this->event->startEvent->copy()->addMinutes(15)))->toBeTrue();
    Notification::assertSentTo($first->user, BookingChanged::class);
});

it('bulk e-mails the pilots of the selected bookings', function (): void {
    EventFacade::fake([EventBulkEmail::class]);
    $booked = filamentBookedSlot($this->event);
    $open = filamentSlot($this->event);

    Livewire::test(ManageEventBookings::class, ['record' => $this->event->getRouteKey()])
        ->selectTableRecords([$booked->id, $open->id])
        ->callAction(TestAction::make('bulkEmail')->table()->bulk(), ['subject' => 'Hi', 'message' => 'Hello'])
        ->assertHasNoActionErrors();

    EventFacade::assertDispatchedTimes(EventBulkEmail::class, 1);
});

it('bulk deletes the selected bookings', function (): void {
    EventFacade::fake([BookingDeleted::class]);
    $first = filamentBookedSlot($this->event);
    $second = filamentSlot($this->event);
    $kept = filamentSlot($this->event);

    Livewire::test(ManageEventBookings::class, ['record' => $this->event->getRouteKey()])
        ->selectTableRecords([$first->id, $second->id])
        ->callAction(TestAction::make('bulkDelete')->table()->bulk());

    $this->assertModelMissing($first);
    $this->assertModelMissing($second);
    $this->assertModelExists($kept);
    EventFacade::assertDispatchedTimes(BookingDeleted::class, 1);
});

it('sends a bulk e-mail to every booked pilot', function (): void {
    EventFacade::fake([EventBulkEmail::class]);
    filamentBookedSlot($this->event);
    filamentBookedSlot($this->event);
    filamentSlot($this->event);

    Livewire::test(ViewEvent::class, ['record' => $this->event->getRouteKey()])
        ->callAction('sendBulkEmail', ['subject' => 'Briefing', 'message' => '<p>See you</p>'])
        ->assertHasNoActionErrors();

    EventFacade::assertDispatched(EventBulkEmail::class, fn (EventBulkEmail $event): bool => $event->users->count() === 2);
});

it('only sends the test bulk e-mail to the admin', function (): void {
    EventFacade::fake([EventBulkEmail::class]);
    filamentBookedSlot($this->event);

    Livewire::test(ViewEvent::class, ['record' => $this->event->getRouteKey()])
        ->callAction('sendBulkEmail', ['subject' => 'Briefing', 'message' => '<p>See you</p>', 'testmode' => true]);

    EventFacade::assertDispatched(EventBulkEmail::class, fn (EventBulkEmail $event): bool => $event->users->modelKeys() === [$this->admin->id]);
});

it('sends the final information e-mail, skipping pilots who had it', function (): void {
    EventFacade::fake([EventFinalInformation::class]);
    filamentBookedSlot($this->event);
    filamentBookedSlot($this->event)->update(['final_information_email_sent_at' => now()]);

    Livewire::test(ViewEvent::class, ['record' => $this->event->getRouteKey()])
        ->callAction('sendFinalInformation', ['forceSend' => false]);

    EventFacade::assertDispatchedTimes(EventFinalInformation::class, 1);
});

it('auto-assigns tracks and levels on oceanic events', function (): void {
    $this->event->update(['is_oceanic_event' => true]);
    $first = filamentBookedSlot($this->event, ['ctot' => $this->event->startEvent]);
    $second = filamentBookedSlot($this->event, ['ctot' => $this->event->startEvent->copy()->addMinutes(5)]);

    Livewire::test(ViewEvent::class, ['record' => $this->event->getRouteKey()])
        ->callAction('autoAssign', [
            'oceanicTrack1' => 'A',
            'route1' => 'ROUTE ONE',
            'oceanicTrack2' => 'B',
            'route2' => 'ROUTE TWO',
            'minFL' => 320,
            'maxFL' => 380,
        ])
        ->assertHasNoActionErrors();

    expect($first->flights()->first())->oceanicTrack->toBe('A')->oceanicFL->toEqual(380)
        ->and($second->flights()->first())->oceanicTrack->toBe('B')->oceanicFL->toEqual(320);
});

it('deletes all bookings of an upcoming event', function (): void {
    filamentSlot($this->event);
    filamentBookedSlot($this->event);

    Livewire::test(ViewEvent::class, ['record' => $this->event->getRouteKey()])
        ->callAction('deleteAllBookings');

    expect($this->event->bookings()->count())->toBe(0);
});

it('imports the selected rows from a parsed file', function (): void {
    Airport::factory()->create(['icao' => 'EHAM']);
    Airport::factory()->create(['icao' => 'EGLL']);

    $component = Livewire::test(ImportBookings::class, ['record' => $this->event->getRouteKey()])
        ->set('rows', [
            ['data' => ['origin' => 'EHAM', 'destination' => 'EGLL', 'call_sign' => 'KLM01', 'aircraft_type' => 'B738'], 'errors' => [], 'valid' => true],
            ['data' => ['origin' => 'EHAM', 'destination' => 'EGLL', 'call_sign' => 'KLM02', 'aircraft_type' => 'B738'], 'errors' => [], 'valid' => true],
            ['data' => ['origin' => 'EHAM', 'destination' => 'ZZZZ', 'call_sign' => 'KLM03'], 'errors' => ['The selected destination is invalid.'], 'valid' => false],
        ]);

    $component
        ->selectTableRecords(['1'])
        ->callAction(TestAction::make('importSelected')->table()->bulk());

    expect($this->event->bookings()->pluck('callsign')->all())->toBe(['KLM02']);
});
