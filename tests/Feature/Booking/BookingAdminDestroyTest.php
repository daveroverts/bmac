<?php

use App\Models\Flight;
use App\Models\User;
use Tests\TestCase;

it('allows an admin to delete a booking with a user', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var User $booker */
    $booker = User::factory()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create();

    $booking = $flight->booking;
    $booking->user_id = $booker->id;
    $booking->save();

    $this->actingAs($admin)
        ->delete(route('admin.bookings.destroy', $booking))
        ->assertRedirect(route('bookings.event.index', $booking->event));

    $this->assertDatabaseMissing('bookings', [
        'id' => $booking->id,
    ]);
});

it('allows an admin to delete a booking without a user', function (): void {
    /** @var TestCase $this */

    /** @var User $admin */
    $admin = User::factory()->admin()->create();

    /** @var Flight $flight */
    $flight = Flight::factory()->create();

    $booking = $flight->booking;

    $this->actingAs($admin)
        ->delete(route('admin.bookings.destroy', $booking))
        ->assertRedirect(route('bookings.event.index', $booking->event));

    $this->assertDatabaseMissing('bookings', [
        'id' => $booking->id,
    ]);
});
