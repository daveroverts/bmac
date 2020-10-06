<?php

namespace Database\Factories;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

class BookingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Booking::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'event_id' => Event::factory()->create(),
            // 'user_id' => User::factory()->make(),
        ];
    }

    /**
     * Indicate that the booking is unassigned.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unassigned()
    {
        return $this->state(function () {
            return [
                'status' => 'unassigned',
            ];
        });
    }

    /**
     * Indicate that the booking is reserved.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function reserved()
    {
        return $this->state(function () {
            return [
                'status' => 'reserved',
            ];
        });
    }

    /**
     * Indicate that the booking is booked.
     *
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function booked()
    {
        return $this->state(function () {
            return [
                'status' => 'booked',
            ];
        });
    }
}
