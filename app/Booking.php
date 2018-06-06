<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    public function airportDep() {
        return $this->hasOne(Airport::class, 'dep', 'icao');
    }

    public function airportArr() {
        return $this->hasOne(Airport::class, 'arr', 'icao');
    }

    public function event() {
        return $this->hasMany(Event::class);
    }

    public function reservedBy() {
        return $this->hasOne(User::class);
    }

    public function bookedBy() {
        return $this->hasOne(User::class);
    }

}
