<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Flight;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Tests\TestCase;

it('can view booking overview for an event', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->create();

    $this->get(route('bookings.event.index', $event))
        ->assertOk()
        ->assertSee($event->name);
});

it('can view a booked flight details', function (): void {
    /** @var TestCase $this */

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create()->id,
    ]);

    $this->get(route('bookings.show', $flight->booking))
        ->assertOk();
});

it('requires authentication to edit a booking', function (): void {
    /** @var TestCase $this */

    /** @var Flight $flight */
    $flight = Flight::factory()->create();
    $booking = $flight->booking;

    $this->from('/')
        ->get(route('bookings.edit', $booking))
        ->assertRedirect();
});

it('allows authenticated users to reserve and edit unassigned bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDay(),
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::UNASSIGNED,
        ])->id,
    ]);

    $this->actingAs($user)
        ->get(route('bookings.edit', $flight->booking))
        ->assertOk();

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::RESERVED);
    expect($flight->booking->user_id)->toBe($user->id);
});

it('allows users to confirm reserved bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->reserved()->create([
            'user_id' => $user->id,
            'is_editable' => true,
        ])->id,
    ]);

    $this->actingAs($user)
        ->patch(route('bookings.update', $flight->booking), [
            'callsign' => 'TEST123',
            'acType' => 'B738',
        ])
        ->assertRedirect(route('bookings.event.index', $flight->booking->event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::BOOKED);
});

it('allows users to cancel their own bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::BOOKED,
            'user_id' => $user->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->patch(route('bookings.cancel', $flight->booking))
        ->assertRedirect(route('bookings.event.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
    expect($flight->booking->user_id)->toBeNull();
});
