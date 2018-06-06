<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    public function bookings() {
        return $this->belongsToMany(Booking::class);
    }
}
