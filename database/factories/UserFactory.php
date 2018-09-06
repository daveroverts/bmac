<?php

use Faker\Generator as Faker;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\Models\User::class, function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(860000,1999999),
        'name_first' => $faker->firstName,
        'name_last' => $faker->lastName,
        'email' => $faker->unique()->safeEmail,
        'country' => 'GB',
        'region' => 'EUR',
        'division' => 'EUD',
        'remember_token' => str_random(10),
    ];
});
