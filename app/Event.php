<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'startEvent',
        'endEvent',
        'startBooking',
        'endBooking'
    ];

    public function bookings() {
        return $this->belongsToMany(Booking::class);
    }
}
