<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(App\Models\Event::class, function (Faker $faker) {
    $name = $faker->name;
    return [
        'name' => $name,
        'slug' => Str::slug($name),
        'description' => $faker->text(),
        'dep' => function () {
        return factory(App\Models\Airport::class)->create()->id;
        },
        'arr' => function () {
            return factory(App\Models\Airport::class)->create()->id;
        },
        'startEvent' => now()->addMonth()->toDateTimeString(),
        'endEvent' => now()->addMonth()->addHours(3)->toDateTimeString(),
        'startBooking' => now()->addWeek()->toDateTimeString(),
        'endBooking' => now()->subDay()->toDateTimeString(),
    ];
});
