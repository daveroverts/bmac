<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'event_id', 'reservedBy_id', 'bookedBy_id', 'callsign', 'acType', 'selcal', 'dep', 'arr', 'ctot', 'oceanicFL', 'oceanicTrack'
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'ctot',
    ];

    /**
     * Format for CTOT
     *
     * @param $value
     * @return string
     */
    public function getCtotAttribute($value)
    {
        return \Carbon\Carbon::parse($value)->format('Hi').'z';
    }

    /**
     * Format for oceanicFL
     *
     * @param $value
     * @return string
     */
    public function getOceanicflAttribute($value)
    {
        if (!empty($value))
        {
            return 'FL'.$value.' / Subject to change';
        }
        else return 'T.B.D.';
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

    public function airportDep() {
        return $this->hasOne(Airport::class, 'icao', 'dep');
    }

    public function airportArr() {
        return $this->hasOne(Airport::class, 'icao', 'arr');
    }

    public function event() {
        return $this->hasOne(Event::class,'id', 'event_id');
    }

    public function reservedBy() {
        return $this->hasOne(User::class, 'id', 'reservedBy_id');
    }

    public function bookedBy() {
        return $this->hasOne(User::class,'id','bookedBy_id');
    }
}
