<?php

use App\Console\Commands\EventCleanupReservationsCommand;
use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Event;
use Tests\TestCase;

it('cleans up expired reservations', function (): void {
    /** @var TestCase $this */

    /** @var Event $event */
    $event = Event::factory()->has(Booking::factory()->count(10))->create();

    $this->assertCount(10, $event->bookings()->get());

    $booking = $event->bookings()->inRandomOrder()->first();

    $booking->status = BookingStatus::RESERVED;
    $booking->save();

    $this->assertCount(1, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());

    $this->artisan(EventCleanupReservationsCommand::class)->assertSuccessful();

    $this->assertCount(1, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());

    $this->travel(Booking::RESERVATION_TIMEOUT_MINUTES - 1)->minutes();

    $this->artisan(EventCleanupReservationsCommand::class)->assertSuccessful();

    $this->assertCount(1, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());

    $this->travel(1)->minutes();

    $this->artisan(EventCleanupReservationsCommand::class)->assertSuccessful();

    $this->assertCount(0, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());
});

it('cleans up reservations for a specific event', function (): void {
    /** @var TestCase $this */

    /** @var Event $eventA */
    $eventA = Event::factory()->has(Booking::factory()->reserved()->count(2))->create();

    /** @var Event $eventB */
    $eventB = Event::factory()->has(Booking::factory()->reserved()->count(3))->create();

    $this->travel(Booking::RESERVATION_TIMEOUT_MINUTES)->minutes();

    $this->artisan(EventCleanupReservationsCommand::class, ['eventId' => $eventA->id])->assertSuccessful();

    $this->assertCount(0, $eventA->bookings()->whereStatus(BookingStatus::RESERVED)->get());
    $this->assertCount(3, $eventB->bookings()->whereStatus(BookingStatus::RESERVED)->get());
});

it('returns failure for non-existent event', function (): void {
    /** @var TestCase $this */

    $this->artisan(EventCleanupReservationsCommand::class, ['eventId' => 99999])
        ->assertFailed();
});
