<?php

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
