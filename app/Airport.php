<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bmac_airports';

    protected $primaryKey = 'icao';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icao', 'iata',
    ];

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
