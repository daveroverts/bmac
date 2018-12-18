<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Booking::class, function (Faker $faker) {
    $event = factory(App\Models\Event::class)->create();
    return [
        'event_id' => $event->id,
        'user_id' => function () {
        factory(App\Models\User::class)->create()->id;
        },
        'dep' => $event->dep,
        'arr' => $event->arr,
    ];
});
