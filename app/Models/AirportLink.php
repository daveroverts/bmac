<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class AirportLink extends Model
{
    use LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'icao_airport', 'airportLinkType_id', 'name', 'url',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function airport()
    {
        return $this->hasOne(Airport::class, 'icao', 'icao_airport');
    }

    public function type()
    {
        return $this->hasOne(AirportLinkType::class, 'id', 'airportLinkType_id');
    }
}
