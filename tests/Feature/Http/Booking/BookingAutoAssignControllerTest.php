<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\Flight;
use App\Models\User;
use Tests\TestCase;

it('prevents non-admin users from accessing auto-assign form', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.events.bookings.autoAssign.create', $event))
        ->assertForbidden();
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

it('prevents non-admin users from submitting auto-assign', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.events.bookings.autoAssign.store', $event), [
            'oceanicTrack1' => 'A',
            'oceanicTrack2' => 'B',
            'route1' => 'ROUTE ONE',
            'route2' => 'ROUTE TWO',
            'minFL' => 310,
            'maxFL' => 390,
        ])
        ->assertForbidden();
});

it('auto-assigns routes and FLs to booked flights only by default', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $bookedBooking = Booking::factory()->booked()->create(['event_id' => $event->id]);
    $bookedFlight = Flight::factory()->create(['booking_id' => $bookedBooking->id]);

    $unassignedBooking = Booking::factory()->create(['event_id' => $event->id]); // UNASSIGNED status
    $unassignedFlight = Flight::factory()->create(['booking_id' => $unassignedBooking->id]);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.autoAssign.store', $event), [
            'oceanicTrack1' => 'A',
            'oceanicTrack2' => 'B',
            'route1' => 'ROUTE ONE',
            'route2' => 'ROUTE TWO',
            'minFL' => 310,
            'maxFL' => 390,
        ])
        ->assertRedirect(route('admin.events.index'));

    // The booked flight should have been assigned
    $this->assertDatabaseHas('flights', [
        'id' => $bookedFlight->id,
        'oceanicTrack' => 'A',
        'route' => 'ROUTE ONE',
        'oceanicFL' => 390, // first (odd) uses maxFL and decrements
    ]);

    // The unassigned flight should not have been changed
    $this->assertDatabaseHas('flights', [
        'id' => $unassignedFlight->id,
        'oceanicTrack' => $unassignedFlight->oceanicTrack,
        'route' => $unassignedFlight->route,
    ]);
});

it('auto-assigns routes and FLs to all flights when checkAssignAllFlights is set', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $bookedBooking = Booking::factory()->booked()->create(['event_id' => $event->id]);
    $bookedFlight = Flight::factory()->create(['booking_id' => $bookedBooking->id]);

    $unassignedBooking = Booking::factory()->create(['event_id' => $event->id]);
    $unassignedFlight = Flight::factory()->create(['booking_id' => $unassignedBooking->id]);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.autoAssign.store', $event), [
            'oceanicTrack1' => 'A',
            'oceanicTrack2' => 'B',
            'route1' => 'ROUTE ONE',
            'route2' => 'ROUTE TWO',
            'minFL' => 310,
            'maxFL' => 390,
            'checkAssignAllFlights' => true,
        ])
        ->assertRedirect(route('admin.events.index'));

    // Both flights should have been assigned
    $this->assertDatabaseHas('flights', [
        'id' => $bookedFlight->id,
        'oceanicTrack' => 'A',
        'route' => 'ROUTE ONE',
    ]);

    $this->assertDatabaseHas('flights', [
        'id' => $unassignedFlight->id,
        'oceanicTrack' => 'B',
        'route' => 'ROUTE TWO',
    ]);
});

it('alternates FL assignment between odd and even bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Event $event */
    $event = Event::factory()->create();

    $booking1 = Booking::factory()->booked()->create(['event_id' => $event->id]);
    $flight1 = Flight::factory()->create(['booking_id' => $booking1->id]);

    $booking2 = Booking::factory()->booked()->create(['event_id' => $event->id]);
    $flight2 = Flight::factory()->create(['booking_id' => $booking2->id]);

    $booking3 = Booking::factory()->booked()->create(['event_id' => $event->id]);
    $flight3 = Flight::factory()->create(['booking_id' => $booking3->id]);

    $this->actingAs($admin)
        ->post(route('admin.events.bookings.autoAssign.store', $event), [
            'oceanicTrack1' => 'A',
            'oceanicTrack2' => 'B',
            'route1' => 'ROUTE ONE',
            'route2' => 'ROUTE TWO',
            'minFL' => 310,
            'maxFL' => 390,
        ])
        ->assertRedirect(route('admin.events.index'));

    // Booking 1 (odd): track 1, maxFL=390
    $this->assertDatabaseHas('flights', ['id' => $flight1->id, 'oceanicTrack' => 'A', 'oceanicFL' => 390]);
    // Booking 2 (even): track 2, minFL=310
    $this->assertDatabaseHas('flights', ['id' => $flight2->id, 'oceanicTrack' => 'B', 'oceanicFL' => 310]);
    // Booking 3 (odd): track 1, maxFL-10=380
    $this->assertDatabaseHas('flights', ['id' => $flight3->id, 'oceanicTrack' => 'A', 'oceanicFL' => 380]);
});
