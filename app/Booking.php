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
}
