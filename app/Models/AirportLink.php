<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AirportLink extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icao_airport', 'airportLinkType_id', 'name', 'slug', 'url'
    ];

    public function airport()
    {
        return $this->hasOne(Airport::class, 'icao', 'icao_airport');
    }

    public function type()
    {
        return $this->hasMany(Airport::class, 'airportLinkType_id');
    }
}
