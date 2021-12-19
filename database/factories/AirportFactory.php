<?php

namespace Database\Factories;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'icao' => strtoupper(Str::random(4)),
            'iata' => strtoupper(Str::random(3)),
            'name' => $this->faker->name.' Airport',
        ];
    }
}
