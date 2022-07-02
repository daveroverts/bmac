<?php

namespace Database\Factories;

use App\Models\Airport;
use App\Models\AirportLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class AirportLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AirportLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'airport_id' => Airport::factory(),
            'airportLinkType_id' => 4,
            'url' => $this->faker->url(),
        ];
    }
}
