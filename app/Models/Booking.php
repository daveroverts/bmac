<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Webpatser\Uuid\Uuid;

class Booking extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id', 'user_id', 'status', 'callsign', 'acType', 'selcal', 'dep', 'arr', 'ctot', 'eta', 'route', 'oceanicFL', 'oceanicTrack'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'ctot', 'eta'
    ];

    /**
     *  Setup model event hooks
     */
    public static function boot()
    {
        parent::boot();
        self::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

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
        if (!empty($value)) {
            return $value;
        } else return '-';
    }

    /**
     * Format for acType
     *
     * @param $value
     * @return string
     */
    public function getActypeAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        } else return '-';
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
        } else return '-';
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
        } else return '-';
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
        } else return 'T.B.D.';
    }

    /**
     * Format for SELCAL
     *
     * @param $value
     * @return string
     */
    public function getSelcalAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        } else return '-';
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
        return $this->hasOne(Airport::class, 'icao', 'dep');
    }

    public function airportArr()
    {
        return $this->hasOne(Airport::class, 'icao', 'arr');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
