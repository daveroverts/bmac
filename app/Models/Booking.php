<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Booking
 *
 * @property int $id
 * @property string|null $uuid
 * @property int $event_id
 * @property int|null $user_id
 * @property int $status
 * @property bool $is_editable
 * @property string $callsign
 * @property string|null $acType
 * @property string $selcal
 * @property \Illuminate\Support\Carbon|null $final_information_email_sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport $airportArr
 * @property-read \App\Models\Airport $airportDep
 * @property-read \App\Models\Event $event
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Flight[] $flights
 * @property-read int|null $flights_count
 * @property string $actype
 * @property-read mixed $has_received_final_information_email
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereAcType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereCallsign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereFinalInformationEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereIsEditable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereSelcal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Booking whereUuid($value)
 * @mixin \Eloquent
 */
class Booking extends Model
{
    use HasFactory;
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
        if ($flight = $this->flights->where('order_by', $orderBy)->first()) {
            if ($withAbbr) {
                return "<abbr title='{$flight->airportDep->name} | [{$flight->airportDep->iata}]'>{$flight->airportDep->icao}</abbr> - <abbr title='{$flight->airportArr->name} | [{$flight->airportArr->iata}]'>{$flight->airportArr->icao}</abbr> {$flight->formattedCtot}";
            }
            return "{$flight->airportDep->icao} - {$flight->airportArr->icao} {$flight->formattedCtot}";
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
