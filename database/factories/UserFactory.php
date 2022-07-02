<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'id' => $this->faker->unique()->numberBetween(860000, 1999999),
            'name_first' => $this->faker->firstName,
            'name_last' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
        ];
    }

    public function admin()
    {
        return $this->state(function (array $attributes) {
            return [
                'isAdmin' => true,
            ];
        });
    }
}
