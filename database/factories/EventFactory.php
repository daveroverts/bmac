<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Event::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->text(),
        'startEvent' => now()->addMonth()->toDateTimeString(),
        'endEvent' => now()->addMonth()->addHours(3)->toDateTimeString(),
        'startBooking' => now()->addWeek()->toDateTimeString(),
        'endBooking' => now()->subDay()->toDateTimeString(),
        'sendFeedbackForm' => now()->addMonth()->addHours(2)->addDay()->toDateTimeString(),
    ];
});
