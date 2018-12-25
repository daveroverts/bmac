<?php

use Faker\Generator as Faker;

$factory->define(App\Models\AirportLink::class, function (Faker $faker) {
    return [
        'airport_id' => function () {
            return factory(App\Models\Airport::class)->create()->id;
        },
        'airportLinkType_id' => 4,
        'url' => $faker->url,
    ];
});
