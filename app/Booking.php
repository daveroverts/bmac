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
        'event_id', 'reservedBy_id', 'bookedBy_id', 'callsign', 'acType', 'selcal', 'dep', 'arr', 'ctot', 'oceanicFL', 'oceanicTrack'
    ];

    public function airportDep() {
        return $this->hasOne(Airport::class, 'icao', 'dep');
    }

    public function airportArr() {
        return $this->hasOne(Airport::class, 'icao', 'arr');
    }

    public function event() {
        return $this->hasOne(Event::class,'id', 'event_id');
    }

    public function reservedBy() {
        return $this->hasOne(User::class, 'id', 'reservedBy_id');
    }

    public function bookedBy() {
        return $this->hasOne(User::class,'id','bookedBy_id');
    }
}
