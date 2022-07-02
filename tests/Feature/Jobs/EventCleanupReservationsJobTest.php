<?php

namespace Tests\Feature\Jobs;

use App\Enums\BookingStatus;
use App\Jobs\EventCleanupReservationsJob;
use App\Models\Booking;
use App\Models\Event;
use Tests\TestCase;

it('cleans up reserverd bookings', function () {
    /** @var TestCase $this  */

    /** @var Event $event */
    $event = Event::factory()->has(Booking::factory()->count(10))->create();

    $this->assertCount(10, $event->bookings()->get());

    $booking = $event->bookings()->inRandomOrder()->first();

    $booking->status = BookingStatus::RESERVED;
    $booking->save();

    $this->assertCount(1, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());

    EventCleanupReservationsJob::dispatch($event);

    $this->assertCount(1, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());

    $this->travel(9)->minutes();

    EventCleanupReservationsJob::dispatch($event);

    $this->assertCount(1, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());

    $this->travel(1)->minutes();

    EventCleanupReservationsJob::dispatch($event);

    $this->assertCount(0, $event->bookings()->whereStatus(BookingStatus::RESERVED)->get());
});
