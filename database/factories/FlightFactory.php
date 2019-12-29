<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Flight::class, function (Faker $faker) {
    $event = factory(App\Models\Event::class)->create();
    $booking = factory(App\Models\Booking::class)->create();
    return [
        'booking_id' => $booking->id,
        'dep' => $event->dep,
        'arr' => $event->arr,
    ];
});
