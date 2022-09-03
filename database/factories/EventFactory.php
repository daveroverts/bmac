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
        $name = $this->faker->sentence();
        $slug = SlugService::createSlug($this->model, 'slug', $name);
        return [
            'name' => $name,
            'slug' => $slug,
            'description' => $this->faker->text(),
            'dep' => Airport::factory(),
            'arr' => Airport::factory(),
            'startEvent' => now()->addMonth(),
            'endEvent' => now()->addMonth()->addHours(3),
            'startBooking' => now()->addWeek(),
            'endBooking' => now()->addMonth()->subHours(12),
        ];
    }

    public function expired()
    {
        return $this->state(function (array $attributes) {
            return [
                'startEvent' => now()->subMonth(),
                'endEvent' => now()->subMonth()->addHours(3),
                'startBooking' => now()->subWeek(),
                'endBooking' => now()->subMonth()->subHours(12),
            ];
        });
    }

    public function onHomePage()
    {
        return $this->state(function (array $attributes) {
            return [
                'show_on_homepage' => true,
            ];
        });
    }

    public function notOnHomePage()
    {
        return $this->state(function (array $attributes) {
            return [
                'show_on_homepage' => false,
            ];
        });
    }
}
