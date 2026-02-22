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

    $this->get(route('events.bookings.index', $event))
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

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->reserved()->create([
            'user_id' => $user->id,
        ])->id,
    ]);

    // Logout the default admin user from TestCase setUp
    auth()->logout();

    $this->from('/')
        ->get(route('bookings.edit', $flight->booking))
        ->assertRedirect('/');
});

it('allows authenticated users to edit their reserved bookings', function (): void {
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
        'booking_id' => Booking::factory()->reserved()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->get(route('bookings.edit', $flight->booking))
        ->assertOk();
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
        ->assertRedirect(route('events.bookings.index', $flight->booking->event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::BOOKED);
});

it('prevents editing bookings after the booking window has closed', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDays(2),
        'endBooking' => now()->subDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->reserved()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->get(route('bookings.edit', $flight->booking))
        ->assertForbidden();
});

it('prevents editing non-editable booked bookings', function (): void {
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
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'is_editable' => false,
        ])->id,
    ]);

    $this->actingAs($user)
        ->get(route('bookings.edit', $flight->booking))
        ->assertRedirect(route('events.bookings.index', $event));
});

it('allows editing editable booked bookings within the booking window', function (): void {
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
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'is_editable' => true,
        ])->id,
    ]);

    $this->actingAs($user)
        ->get(route('bookings.edit', $flight->booking))
        ->assertOk();
});

it('prevents users from editing bookings they do not own', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDay(),
        'endBooking' => now()->addDay(),
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->reserved()->create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->get(route('bookings.edit', $flight->booking))
        ->assertForbidden();
});
