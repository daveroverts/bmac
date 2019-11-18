<?php

namespace App\Models;

use App\Enums\AirportView;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Airport extends Model
{
    use LogsActivity;

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
        return $this->hasMany(AirportLink::class);
    }

    public function getFullNameAttribute()
    {
        if (auth()->check() && auth()->user()->airport_view !== AirportView::NAME) {
            switch (auth()->user()->airport_view) {
                case AirportView::ICAO:
                    return '<abbr title="' . $this->name . ' | [' . $this->iata . ']">' . $this->icao . '</abbr>';
                    break;
                case AirportView::IATA:
                    return '<abbr title="' . $this->name . ' | [' . $this->icao . ']">' . $this->iata . '</abbr>';
                    break;
            }
        }
        return '<abbr title="' . $this->icao . ' | [' . $this->iata . ']">' . $this->name . '</abbr>';
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'icao';
    }
}
