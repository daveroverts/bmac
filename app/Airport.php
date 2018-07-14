<?php

namespace App;

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
        return $this->belongsToMany(Booking::class, 'events', 'dep');
    }

    public function bookingsArr()
    {
        return $this->belongsToMany(Booking::class, 'events', 'arr');
    }
}
