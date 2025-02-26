<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

class FaqFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'question' => $this->faker->sentence() . '?',
            'answer' => $this->faker->sentence(),
        ];
    }

    public function offline()
    {
        return $this->state(fn (array $attributes) => [
            'is_online' => false,
        ]);
    }
}
