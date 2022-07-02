<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventLink;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventLinkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = EventLink::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event_id' => Event::factory(),
            'event_link_type_id' => 4,
            'url' => $this->faker->url(),
        ];
    }
}
