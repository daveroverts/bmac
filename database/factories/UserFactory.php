<?php

namespace Database\Factories;

use App\Models\User;
use App\Enums\AirportView;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
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
            'id' => fake()->unique()->numberBetween(860000, 1999999),
            'name_first' => fake()->firstName(),
            'name_last' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
        ];
    }

    public function admin()
    {
        return $this->state(fn (array $attributes) => [
            'isAdmin' => true,
        ]);
    }

    public function airportViewName()
    {
        return $this->state(fn (array $attributes) => [
            'airport_view' => AirportView::NAME,
        ]);
    }

    public function airportViewIcao()
    {
        return $this->state(fn (array $attributes) => [
            'airport_view' => AirportView::ICAO,
        ]);
    }

    public function airportViewIata()
    {
        return $this->state(fn (array $attributes) => [
            'airport_view' => AirportView::IATA,
        ]);
    }

    public function monospaceFont()
    {
        return $this->state(fn (array $attributes) => [
            'monospace_font' => true,
        ]);
    }
}
