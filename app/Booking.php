<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bmac_bookings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id', 'reservedBy_id', 'bookedBy_id', 'callsign', 'acType', 'selCal', 'dep', 'arr', 'ctot', 'oceanicFL'
    ];

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
