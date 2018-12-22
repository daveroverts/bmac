<?php

namespace App\Models;

use App\Enums\AirportView;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
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
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

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

    public function getFullNameAttribute()
    {
        if (Auth::check()) {
            if (Auth::user()->airport_view == AirportView::IATA) {
                return '<abbr title="' . $this->name . ' | [' . $this->icao . ']">' . $this->iata . '</abbr>';
            }
            if (Auth::user()->airport_view == AirportView::NAME) {
                return '<abbr title="' . $this->icao . ' | [' . $this->iata . ']">' . $this->name . '</abbr>';
            }
        }
        return '<abbr title="' . $this->name . ' | [' . $this->iata . ']">' . $this->icao . '</abbr>';
    }
}
