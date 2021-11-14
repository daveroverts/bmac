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
 * @property int|null $dep
 * @property int|null $arr
 * @property \Illuminate\Support\Carbon|null $ctot
 * @property \Illuminate\Support\Carbon|null $eta
 * @property string|null $route
 * @property string|null $notes
 * @property string|null $oceanicFL
 * @property string|null $oceanicTrack
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport $airportArr
 * @property-read \App\Models\Airport $airportDep
 * @property-read \App\Models\Booking $booking
 * @property-read string $formatted_ctot
 * @property-read string $formatted_eta
 * @property-read string $formatted_notes
 * @property-read string $formatted_oceanicfl
 * @property-write mixed $oceanictrack
 * @method static \Database\Factories\FlightFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Flight query()
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereArr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereCtot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereDep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereEta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereOceanicFL($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereOceanicTrack($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereOrderBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Flight whereUpdatedAt($value)
 * @mixin \Eloquent
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
     * @return string
     */
    public function getFormattedOceanicflAttribute()
    {
        if ($this->oceanicFL) {
            return 'FL' . $this->oceanicFL;
        }

        return '-';
    }

    /**
     * Format for notes
     *
     * @return string
     */
    public function getFormattedNotesAttribute()
    {
        return $this->notes ?: '-';
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
        return $this->hasOne(Airport::class, 'id', 'dep')->withDefault();
    }

    public function airportArr()
    {
        return $this->hasOne(Airport::class, 'id', 'arr')->withDefault();
    }
}
