<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Airport extends Model
{

    public $incrementing = false;
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    protected $primaryKey = 'icao';
    protected $keyType = 'string';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icao', 'iata', 'name',
    ];

    public function bookingsDep()
    {
        return $this->hasMany(Booking::class, 'dep');
    }

    public function bookingsArr()
    {
        return $this->hasMany(Booking::class, 'arr');
    }

    public function links()
    {
        return $this->hasMany(AirportLink::class, 'icao_airport');
    }
}
