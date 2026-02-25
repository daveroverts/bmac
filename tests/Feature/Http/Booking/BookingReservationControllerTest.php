<?php

use App\Models\User;
use App\Models\Event;
use App\Models\Flight;
use App\Models\Booking;
use App\Enums\BookingStatus;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

it('requires authentication to reserve a booking', function (): void {
    /** @var TestCase $this */

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->unassigned()->create()->id,
    ]);

    $this->post(route('bookings.reservation.store', $flight->booking))
        ->assertRedirect();
});

it('allows authenticated users to reserve an unassigned booking', function (): void {
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
        ->post(route('bookings.reservation.store', $flight->booking))
        ->assertRedirect(route('bookings.edit', $flight->booking));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::RESERVED);
    expect($flight->booking->user_id)->toBe($user->id);
});

it('prevents reservation when booking window is closed', function (): void {
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
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::UNASSIGNED,
        ])->id,
    ]);

    $this->actingAs($user)
        ->post(route('bookings.reservation.store', $flight->booking))
        ->assertForbidden();

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
    expect($flight->booking->user_id)->toBeNull();
});

it('prevents reservation when user already has a reservation for the event', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDay(),
        'endBooking' => now()->addDay(),
    ]);

    // User already has a reservation
    Booking::factory()->reserved()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::UNASSIGNED,
        ])->id,
    ]);

    $this->actingAs($user)
        ->post(route('bookings.reservation.store', $flight->booking))
        ->assertRedirect(route('events.bookings.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
    expect($flight->booking->user_id)->toBeNull();
});

it('prevents reservation when event does not allow multiple bookings and user already has a booking', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDay(),
        'endBooking' => now()->addDay(),
        'multiple_bookings_allowed' => false,
    ]);

    // User already has a confirmed booking
    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::UNASSIGNED,
        ])->id,
    ]);

    $this->actingAs($user)
        ->post(route('bookings.reservation.store', $flight->booking))
        ->assertRedirect(route('events.bookings.index', $event));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::UNASSIGNED);
    expect($flight->booking->user_id)->toBeNull();
});

it('allows reservation when event allows multiple bookings', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDay(),
        'endBooking' => now()->addDay(),
        'multiple_bookings_allowed' => true,
    ]);

    // User already has a confirmed booking
    Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
    ]);

    /** @var Flight $flight */
    $flight = Flight::factory()->create([
        'booking_id' => Booking::factory()->create([
            'event_id' => $event->id,
            'status' => BookingStatus::UNASSIGNED,
        ])->id,
    ]);

    $this->actingAs($user)
        ->post(route('bookings.reservation.store', $flight->booking))
        ->assertRedirect(route('bookings.edit', $flight->booking));

    $flight->booking->refresh();
    expect($flight->booking->status)->toBe(BookingStatus::RESERVED);
    expect($flight->booking->user_id)->toBe($user->id);
});

it('prevents reservation of already reserved bookings', function (): void {
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
        ->post(route('bookings.reservation.store', $flight->booking))
        ->assertForbidden();

    $flight->booking->refresh();
    expect($flight->booking->user_id)->toBe($otherUser->id);
});

it('prevents reservation of already booked bookings', function (): void {
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
        'booking_id' => Booking::factory()->booked()->create([
            'event_id' => $event->id,
            'user_id' => $otherUser->id,
        ])->id,
    ]);

    $this->actingAs($user)
        ->post(route('bookings.reservation.store', $flight->booking))
        ->assertForbidden();

    $flight->booking->refresh();
    expect($flight->booking->user_id)->toBe($otherUser->id);
});

it('handles a race condition where another request claims the slot between policy check and update', function (): void {
    /** @var TestCase $this */

    /** @var User $userA */
    $userA = User::factory()->create();
    /** @var User $userB */
    $userB = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->create([
        'startBooking' => now()->subDay(),
        'endBooking' => now()->addDay(),
    ]);

    $booking = Booking::factory()->create([
        'event_id' => $event->id,
        'status' => BookingStatus::UNASSIGNED,
    ]);

    Flight::factory()->create(['booking_id' => $booking->id]);

    // Bypass the policy so we can reach the atomic update in the controller.
    // The race condition happens after the policy check passes but before the update.
    Gate::before(fn (User $user, string $ability): ?bool => $ability === 'reserve' ? true : null);

    // Simulate userB winning the race by claiming the slot just before userA's request lands.
    Booking::query()
        ->where('id', $booking->id)
        ->where('status', BookingStatus::UNASSIGNED)
        ->update(['status' => BookingStatus::RESERVED, 'user_id' => $userB->id]);

    // userA's atomic update should return 0 rows affected and redirect back with a warning.
    $this->actingAs($userA)
        ->post(route('bookings.reservation.store', $booking))
        ->assertRedirect(route('events.bookings.index', $event));

    $booking->refresh();
    // Slot must still belong to userB
    expect($booking->status)->toBe(BookingStatus::RESERVED);
    expect($booking->user_id)->toBe($userB->id);
});
