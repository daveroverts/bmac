<?php

use App\Models\Event;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Faker\Generator as Faker;

$factory->define(App\Models\Event::class, function (Faker $faker) {
    $name = $faker->sentence;
    $slug = SlugService::createSlug(Event::class, 'slug', $name);
    return [
        'name' => $name,
        'slug' => $slug,
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
        'endBooking' => now()->addMonth()->subHours(12)->toDateTimeString(),
    ];
});
