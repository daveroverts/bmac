<?php

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Tests\TestCase;

it('shows online homepage events in the navbar for guests', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->onHomePage()->create();

    $this->get('/')
        ->assertOk()
        ->assertSee($event->name);
});

it('shows user bookings in the navbar for authenticated users', function (): void {
    /** @var TestCase $this */

    /** @var User $user */
    $user = User::factory()->create();

    /** @var Event $event */
    $event = Event::factory()->onHomePage()->create();

    /** @var Booking $booking */
    $booking = Booking::factory()->booked()->create([
        'event_id' => $event->id,
        'user_id' => $user->id,
        'callsign' => 'TEST123',
    ]);

    $this->actingAs($user)
        ->get('/')
        ->assertOk()
        ->assertSee($event->name)
        ->assertSee($booking->callsign);
});

it('does not show expired events in the navbar', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->expired()->onHomePage()->create();

    $this->get('/')
        ->assertOk()
        ->assertDontSee($event->name);
});

it('does not show offline events in the navbar', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->onHomePage()->create([
        'is_online' => false,
    ]);

    $this->get('/')
        ->assertOk()
        ->assertDontSee($event->name);
});
