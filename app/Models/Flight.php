<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport $airportArr
 * @property-read \App\Models\Airport $airportDep
 * @property-read \App\Models\Booking $booking
 * @property-read string $formatted_ctot
 * @property-read string $formatted_eta
 * @property-read string $formatted_notes
 * @property-read string $formatted_oceanicfl
 * @property-write mixed $oceanictrack
 * @method static \Database\Factories\FlightFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereArr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereBookingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereCtot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereDep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereEta($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereOceanicFL($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereOceanicTrack($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereOrderBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereRoute($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Flight whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Flight extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = ['id'];

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

    protected function getFormattedCtotAttribute(): string
    {
        if (!empty($this->ctot)) {
            return $this->ctot->format('Hi') . 'z';
        }

        return '-';
    }

    protected function getFormattedEtaAttribute(): string
    {
        if (!empty($this->eta)) {
            return $this->eta->format('Hi') . 'z';
        }

        return '-';
    }

    protected function getFormattedOceanicflAttribute(): string
    {
        if ($this->oceanicFL) {
            return 'FL' . $this->oceanicFL;
        }

        return '-';
    }

    protected function getFormattedNotesAttribute(): string
    {
        return $this->notes ?: '-';
    }

    protected function setRouteAttribute($value): void
    {
        $this->attributes['route'] = empty($value) ? null : strtoupper((string) $value);
    }

    protected function setOceanictrackAttribute($value): void
    {
        $this->attributes['oceanicTrack'] = empty($value) ? null : strtoupper((string) $value);
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

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ctot' => 'datetime',
            'eta' => 'datetime',
        ];
    }
}
