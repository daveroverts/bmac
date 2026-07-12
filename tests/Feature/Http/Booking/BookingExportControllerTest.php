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

    /** @var Booking $booking */
    $booking = Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => User::factory()->create()->id,
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
        ->toContain('16:45');
});

it('exports multiflights bookings for a vACC with user emails and no times', function (): void {
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
        'callsign' => 'MULTI2',
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
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.events.bookings.export', ['event' => $event, 'vacc' => 'vacc']))
        ->assertOk()
        ->assertDownload('bookings.csv');

    $content = file_get_contents($response->getFile()->getPathname());
    expect($content)->toContain('MULTI2')
        ->toContain($bookedUser->email)
        ->toContain($event->airportDep->icao)
        ->not->toContain('14:30');
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
