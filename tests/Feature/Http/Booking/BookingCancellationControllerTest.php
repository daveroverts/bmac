<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Flight;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Tests\TestCase;

it('requires authentication to cancel a booking', function (): void {
    /** @var TestCase $this */

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create()->id,
    ]);

    $this->delete(route('bookings.cancellation.destroy', $flight->booking))
        ->assertRedirect();
});

it('allows users to cancel their own booked booking', function (): void {
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
        ->delete(route('bookings.cancellation.destroy', $flight->booking))
        ->assertRedirect(route('events.bookings.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
    expect($flight->booking->user_id)->toBeNull();
});

it('allows users to cancel their own reserved booking', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->reserved()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->delete(route('bookings.cancellation.destroy', $flight->booking))
        ->assertRedirect(route('events.bookings.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
    expect($flight->booking->user_id)->toBeNull();
});

it('prevents cancelling a booking after the booking window has closed', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'endBooking' => now()->subDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->delete(route('bookings.cancellation.destroy', $flight->booking))
        ->assertForbidden();

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::BOOKED);
    expect($flight->booking->user_id)->toBe($user->id);
});

it('prevents cancelling a booking owned by another user', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->delete(route('bookings.cancellation.destroy', $flight->booking))
        ->assertForbidden();

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::BOOKED);
    expect($flight->booking->user_id)->toBe($otherUser->id);
});

it('clears editable fields when cancelling an editable booking', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'is_editable' => true,
            'callsign' => 'TEST123',
            'acType' => 'B738',
        ])->id,
    ]);

    $this->actingAs($user)
        ->delete(route('bookings.cancellation.destroy', $flight->booking))
        ->assertRedirect(route('events.bookings.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->callsign)->toBeNull();
    expect($flight->booking->acType)->toBeNull();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
});

it('clears selcal when cancelling a non-editable booking', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'is_editable' => false,
            'callsign' => 'TEST123',
            'acType' => 'B738',
            'selcal' => 'AB-CD',
        ])->id,
    ]);

    $this->actingAs($user)
        ->delete(route('bookings.cancellation.destroy', $flight->booking))
        ->assertRedirect(route('events.bookings.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->callsign)->toBe('TEST123')
        ->and($flight->booking->acType)->toBe('B738')
        ->and($flight->booking->selcal)->toBeNull()
        ->and($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
});
