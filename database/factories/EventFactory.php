<?php

namespace Database\Factories;

use App\Models\Airport;
use App\Models\Event;
use Cviebrock\EloquentSluggable\Services\SlugService;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $name = $this->faker->sentence;
        $slug = SlugService::createSlug($this->model, 'slug', $name);
        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->text(),
            'dep' => Airport::factory(),
            'arr' => Airport::factory(),
            'startEvent' => now()->addMonth()->toDateTimeString(),
            'endEvent' => now()->addMonth()->addHours(3)->toDateTimeString(),
            'startBooking' => now()->addWeek()->toDateTimeString(),
            'endBooking' => now()->addMonth()->subHours(12)->toDateTimeString(),
        ];
    }
}
