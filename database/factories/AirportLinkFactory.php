<?php

use Faker\Generator as Faker;

$factory->define(App\Models\AirportLink::class, function (Faker $faker) {
    return [
        'icao_airport' => function () {
            return factory(App\Models\Airport::class)->create()->icao;
        },
        'airportLinkType_id' => 4,
        'url' => $faker->url,
    ];
});
