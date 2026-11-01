<?php

use App\Enums\EventType;
use App\Models\Airport;
use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use Tests\TestCase;

it('exports CSV containing booked booking data', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var User $bookedUser */
    $bookedUser = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    /** @var Booking $booking */
    $booking = Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => $bookedUser->id,
        'callsign' => 'EXP001',
        'acType' => 'B738',
    ]);

    Flight::factory()->create([
        'booking_id' => $booking->id,
        'dep' => $event->airportDep->id,
        'arr' => $event->airportArr->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', $event))
        ->assertOk()
        ->assertDownload('bookings.csv');

    $content = file_get_contents($response->getFile()->getPathname());
    expect($content)->toContain($bookedUser->full_name)
        ->toContain('EXP001')
        ->toContain('B738')
        ->toContain($event->airportDep->icao)
        ->toContain($event->airportArr->icao);
});

it('exports ctot and eta as times in CSV', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    /** @var Booking $booking */
    $booking = Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
    ]);

    Flight::factory()->create([
        'booking_id' => $booking->id,
        'dep' => $event->airportDep->id,
        'arr' => $event->airportArr->id,
        'ctot' => $event->startEvent->copy()->setTime(14, 30),
        'eta' => $event->startEvent->copy()->setTime(15, 45),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', $event))
        ->assertOk()
        ->assertDownload('bookings.csv');

    $content = file_get_contents($response->getFile()->getPathname());
    expect($content)->toContain('14:30')
        ->toContain('15:45');
});

it('exports multiflights bookings with both legs and ctot times', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create(['event_type_id' => EventType::MULTIFLIGHTS->value]);

    $airportVia = Airport::factory()->create();

    /** @var User $bookedUser */
    $bookedUser = User::factory()->create();

    /** @var Booking $booking */
    $booking = Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => $bookedUser->id,
        'callsign' => 'MULTI1',
    ]);

    $booking->flights()->createMany([
        [
            'order_by' => 1,
            'dep' => $event->airportDep->id,
            'arr' => $airportVia->id,
            'ctot' => $event->startEvent->copy()->setTime(14, 30),
        ],
        [
            'order_by' => 2,
            'dep' => $airportVia->id,
            'arr' => $event->airportArr->id,
            'ctot' => $event->startEvent->copy()->setTime(16, 45),
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', $event))
        ->assertOk()
        ->assertDownload('bookings.csv');

    $content = file_get_contents($response->getFile()->getPathname());
    expect($content)->toContain('MULTI1')
        ->toContain($event->airportDep->icao)
        ->toContain($airportVia->icao)
        ->toContain($event->airportArr->icao)
        ->toContain('14:30')
        ->toContain('16:45')
        ->not->toContain($bookedUser->email);
});

it('only includes booked bookings in CSV export', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $bookedUser = User::factory()->create();
    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => $bookedUser->id,
        'callsign' => 'BOOKED',
    ])->flights()->create([
        'dep' => $event->airportDep->id,
        'arr' => $event->airportArr->id,
    ]);

    Booking::factory()->unassigned()->create([
        'event_id' => $event->id,
        'callsign' => 'UNASGN',
    ])->flights()->create([
        'dep' => $event->airportDep->id,
        'arr' => $event->airportArr->id,
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', $event))
        ->assertOk()
        ->assertDownload('bookings.csv');

    $content = file_get_contents($response->getFile()->getPathname());
    expect($content)->toContain('BOOKED')
        ->not->toContain('UNASGN');
});

it('exports VATCAN slots as CSV with cid and slot columns', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create();

    $booked = Booking::factory()->booked()->create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id]);
    Flight::factory()->create([
        'booking_id' => $booked->id,
        'ctot' => $event->startEvent->copy()->setTime(14, 30),
    ]);
    $unbooked = Booking::factory()->create(['event_id' => $event->id]);
    Flight::factory()->create(['booking_id' => $unbooked->id]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', [$event, 'format' => 'vatcan-csv']))
        ->assertOk()
        ->assertDownload('vatcan-slots.csv');

    $lines = array_map(trim(...), file($response->getFile()->getPathname()));

    expect($lines)->toBe(['cid,slot', $booked->user_id . ',1430']);
});

it('exports VATCAN slots as JSON with an integer cid and a nullable slot', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create();

    $withSlot = Booking::factory()->booked()->create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id]);
    Flight::factory()->create([
        'booking_id' => $withSlot->id,
        'ctot' => $event->startEvent->copy()->setTime(14, 30),
    ]);
    $withoutSlot = Booking::factory()->booked()->create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id]);
    Flight::factory()->create(['booking_id' => $withoutSlot->id, 'ctot' => null]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', [$event, 'format' => 'vatcan-json']))
        ->assertOk();

    expect($response->headers->get('Content-Disposition'))->toContain('vatcan-slots.json')
        ->and($response->json())->toBe([
            ['cid' => $withSlot->user_id, 'slot' => '1430'],
            ['cid' => $withoutSlot->user_id, 'slot' => null],
        ]);
});

it('rejects an unknown export format', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create();

    $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', [$event, 'format' => 'xml']))
        ->assertSessionHasErrors('format');
});

it('prevents non-admin users from exporting VATCAN slots', function (): void {
    /** @var TestCase $this */
    $event = Event::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.events.bookings.export', [$event, 'format' => 'vatcan-json']))
        ->assertForbidden();
});

it('exports only the header row in the VATCAN CSV when nothing is booked', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create();

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', [$event, 'format' => 'vatcan-csv']))
        ->assertOk();

    expect(array_map(trim(...), file($response->getFile()->getPathname())))->toBe(['cid,slot']);
});

it('orders VATCAN slots by CTOT across midnight with empty slots last', function (): void {
    /** @var TestCase $this */
    $admin = User::factory()->admin()->create();
    $event = Event::factory()->create([
        'startEvent' => '2026-07-15 22:00:00',
        'endEvent' => '2026-07-16 02:00:00',
    ]);

    $slots = ['2026-07-16 00:15:00', null, '2026-07-15 23:30:00'];

    foreach ($slots as $ctot) {
        $booking = Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => User::factory()->create()->id,
        ]);
        Flight::factory()->create(['booking_id' => $booking->id, 'ctot' => $ctot]);
    }

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', [$event, 'format' => 'vatcan-json']))
        ->assertOk();

    expect(array_column($response->json(), 'slot'))->toBe(['2330', '0015', null]);
});
