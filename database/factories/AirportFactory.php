<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Airport::class, function (Faker $faker) {
    return [
        'icao' => strtoupper(str_random(4)),
        'iata' => strtoupper(str_random(3)),
        'name' => $faker->name . ' Airport',
    ];
});
