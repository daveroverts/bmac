<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;

class Booking extends Model
{
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'uuid', 'status', 'selcal',
    ];

    protected $casts = [
        'is_editable' => 'boolean'
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string)Str::uuid();
        });
    }

    /**
     * The relations to eager load on every query.
     *
     * @var array
     */
    protected $with = ['flights'];

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'uuid';
    }

    /**
     * Format for callsign
     *
     * @param $value
     * @return string
     */
    public function getCallsignAttribute($value)
    {
        return $value ?? '-';
    }

    /**
     * Format for acType
     *
     * @param $value
     * @return string
     */
    public function getActypeAttribute($value)
    {
        return $value ?? '-';
    }

    /**
     * Format for CTOT
     *
     * @param $value
     * @return string
     */
    public function getCtotAttribute($value)
    {
        if (!empty($value)) {
            return \Carbon\Carbon::parse($value)->format('Hi') . 'z';
        }

        return '-';
    }

    /**
     * Format for ETA
     *
     * @param $value
     * @return string
     */
    public function getEtaAttribute($value)
    {
        if (!empty($value)) {
            return \Carbon\Carbon::parse($value)->format('Hi') . 'z';
        }

        return '-';
    }

    /**
     * Format for oceanicFL
     *
     * @param $value
     * @return string
     */
    public function getOceanicflAttribute($value)
    {
        if (!empty($value)) {
            return 'FL' . $value . ' / Subject to change';
        }

        return 'T.B.D.';
    }

    /**
     * Format for SELCAL
     *
     * @param $value
     * @return string
     */
    public function getSelcalAttribute($value)
    {
        return $value ?? '-';
    }

    /**
     * Capitalize Callsign
     *
     * @param $value
     */
    public function setCallsignAttribute($value)
    {
        $this->attributes['callsign'] = strtoupper($value);
    }

    /**
     * Capitalize Route
     *
     * @param $value
     */
    public function setRouteAttribute($value)
    {
        $this->attributes['route'] = strtoupper($value);
    }

    /**
     * Capitalize Aircraft
     *
     * @param $value
     */
    public function setActypeAttribute($value)
    {
        $this->attributes['acType'] = strtoupper($value);
    }

    /**
     * Capitalize SELCAL
     *
     * @param $value
     */
    public function setSelcalAttribute($value)
    {
        $this->attributes['selcal'] = strtoupper($value);
    }

    /**
     * Capitalize Oceanic Track
     *
     * @param $value
     */
    public function setOceanictrackAttribute($value)
    {
        $this->attributes['oceanicTrack'] = strtoupper($value);
    }

    public function airportDep()
    {
        return $this->hasOne(Airport::class, 'id', 'dep');
    }

    public function airportArr()
    {
        return $this->hasOne(Airport::class, 'id', 'arr');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function flights()
    {
        return $this->hasMany(Flight::class);
    }

    public function airportCtot($orderBy, $withAbbr = true)
    {
        if ($flight = $this->flights()->where('order_by', $orderBy)->first()) {
            if ($withAbbr) {
                return "<abbr title='{$flight->airportDep->name} | [{$flight->airportDep->iata}]'>{$flight->airportDep->icao}</abbr> - <abbr title='{$flight->airportArr->name} | [{$flight->airportArr->iata}]'>{$flight->airportArr->icao}</abbr> {$flight->ctot}";
            }
            return "{$flight->airportDep->icao} - {$flight->airportArr->icao} {$flight->ctot}";
        }
        return '-';
    }

    public function uniqueAirports()
    {
        $airports = collect();
        $this->flights()->each(function ($flight) use ($airports) {
            /* @var Flight $flight */
            $airports->push($flight->airportDep);
            $airports->push($flight->airportArr);
        });

        return $airports->unique();
    }
}
