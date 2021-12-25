<?php

namespace Database\Factories;

use App\Models\Airport;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Airport::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'icao' => $this->faker->unique()->regexify('[A-Z]{4}'),
            'iata' => $this->faker->unique()->regexify('[A-Z]{3}'),
            'name' => $this->faker->name . ' Airport',
        ];
    }
}
