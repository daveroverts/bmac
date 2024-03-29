<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Flight::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $booking = Booking::factory()->create();
        return [
            'booking_id' => $booking->id,
            'dep' => $booking->event->dep,
            'arr' => $booking->event->arr,
        ];
    }
}
