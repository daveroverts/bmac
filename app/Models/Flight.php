<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * App\Models\Flight
 *
 * @property int $id
 * @property int $booking_id
 * @property int $order_by
 * @property int $dep
 * @property int $arr
 * @property string $ctot
 * @property string $eta
 * @property string|null $route
 * @property string $notes
 * @property string|null $oceanicFL
 * @property string|null $oceanicTrack
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport $airportArr
 * @property-read \App\Models\Airport $airportDep
 * @property-read \App\Models\Booking $booking
 * @property-read string $oceanicfl
 * @property-write mixed $oceanictrack
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereArr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereCtot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereDep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereEta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereOceanicFL($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereOceanicTrack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereOrderBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Flight whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read string $formatted_ctot
 * @property-read string $formatted_eta
 */
class Flight extends Model
{
    use HasFactory;
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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['ctot', 'eta'];

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
    public function getFormattedCtotAttribute()
    {
        if (!empty($this->ctot)) {
            return $this->ctot->format('Hi') . 'z';
        }
        return '-';
    }

    /**
     * Format for ETA
     *
     * @return string
     */
    public function getFormattedEtaAttribute()
    {
        if (!empty($this->eta)) {
            return $this->eta->format('Hi') . 'z';
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
        $this->attributes['route'] = !empty($value) ? strtoupper($value) : null;
    }

    /**
     * Capitalize Oceanic Track
     *
     * @param $value
     */
    public function setOceanictrackAttribute($value)
    {
        $this->attributes['oceanicTrack'] = !empty($value) ? strtoupper($value) : null;
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
