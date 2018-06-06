<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{
    protected $primaryKey = 'icao';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function bookingsDep() {
        return $this->belongsToMany(Booking::class, 'events', 'dep');
    }

    public function bookingsArr() {
        return $this->belongsToMany(Booking::class, 'events', 'arr');
    }
}
