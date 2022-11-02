<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

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
 * @property-read Airport $airportArr
 * @property-read Airport $airportDep
 * @property-read Booking $booking
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

    protected $guarded = ['id'];

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function getFormattedCtotAttribute(): string
    {
        if (!empty($this->ctot)) {
            return $this->ctot->format('Hi') . 'z';
        }
        return '-';
    }

    public function getFormattedEtaAttribute(): string
    {
        if (!empty($this->eta)) {
            return $this->eta->format('Hi') . 'z';
        }
        return '-';
    }

    public function getFormattedOceanicflAttribute(): string
    {
        if ($this->oceanicFL) {
            return 'FL' . $this->oceanicFL;
        }

        return '-';
    }

    public function getFormattedNotesAttribute(): string
    {
        return $this->notes ?: '-';
    }

    public function setRouteAttribute($value): void
    {
        $this->attributes['route'] = !empty($value) ? strtoupper($value) : null;
    }

    public function setOceanictrackAttribute($value): void
    {
        $this->attributes['oceanicTrack'] = !empty($value) ? strtoupper($value) : null;
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function airportDep(): HasOne
    {
        return $this->hasOne(Airport::class, 'id', 'dep')->withDefault();
    }

    public function airportArr(): HasOne
    {
        return $this->hasOne(Airport::class, 'id', 'arr')->withDefault();
    }
}
