<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Faq::class, function (Faker $faker) {
    return [
        'question' => $faker->sentence . '?',
        'answer' => $faker->sentence(),
    ];
});
