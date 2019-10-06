<?php

use Faker\Generator as Faker;
use Illuminate\Support\Str;

$factory->define(App\Models\Airport::class, function (Faker $faker) {
    return [
        'icao' => strtoupper(Str::random(4)),
        'iata' => strtoupper(Str::random(3)),
        'name' => $faker->name . ' Airport',
    ];
});
