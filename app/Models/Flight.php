<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

class Flight extends Model
{
    use LogsActivity;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['booking'];

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
     * Format for notes
     *
     * @param $value
     * @return string
     */
    public function getNotesAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        return '-';
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
     * Capitalize Oceanic Track
     *
     * @param $value
     */
    public function setOceanictrackAttribute($value)
    {
        $this->attributes['oceanicTrack'] = strtoupper($value);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function airportDep()
    {
        return $this->hasOne(Airport::class, 'id', 'dep');
    }

    public function airportArr()
    {
        return $this->hasOne(Airport::class, 'id', 'arr');
    }
}
