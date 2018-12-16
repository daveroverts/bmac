<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Airport extends Model
{
    use LogsActivity;

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

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function bookingsDep()
    {
        return $this->hasMany(Booking::class, 'dep');
    }

    public function bookingsArr()
    {
        return $this->hasMany(Booking::class, 'arr');
    }

    public function eventDep()
    {
        return $this->hasMany(Event::class, 'dep');
    }

    public function eventArr()
    {
        return $this->hasMany(Event::class, 'arr');
    }

    public function links()
    {
        return $this->hasMany(AirportLink::class, 'icao_airport');
    }
}
