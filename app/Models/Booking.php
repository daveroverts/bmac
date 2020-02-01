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
        'is_editable' => 'boolean',
        'has_already_received_final_information_email' => 'boolean',
    ];

    protected $dates = [
        'final_information_email_sent_at',
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
//    protected $with = ['flights'];

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
     * Format for SELCAL
     *
     * @param $value
     * @return string
     */
    public function getSelcalAttribute($value)
    {
        return $value ?? '-';
    }

    /*
     * Determine if the FinalInformationEmail was already sent or not
     * @return bool
     * */
    public function getHasReceivedFinalInformationEmailAttribute($value) {
        return !empty($this->final_information_email_sent_at);
    }

    /**
     * Capitalize Callsign
     *
     * @param $value
     */
    public function setCallsignAttribute($value)
    {
        $this->attributes['callsign'] = !empty($value) ? strtoupper($value) : null;
    }

    /**
     * Capitalize Aircraft
     *
     * @param $value
     */
    public function setActypeAttribute($value)
    {
        $this->attributes['acType'] = !empty($value) ? strtoupper($value) : null;
    }

    /**
     * Capitalize SELCAL
     *
     * @param $value
     */
    public function setSelcalAttribute($value)
    {
        $this->attributes['selcal'] = !empty($value) ? strtoupper($value) : null;
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
        return $this->belongsTo(User::class)->withDefault();
    }

    public function flights()
    {
        return $this->hasMany(Flight::class)->orderBy('order_by');
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
