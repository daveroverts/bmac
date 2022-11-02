<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Booking
 *
 * @property int $id
 * @property string|null $uuid
 * @property int $event_id
 * @property int $status
 * @property bool $is_editable
 * @property int|null $user_id
 * @property string|null $callsign
 * @property string|null $acType
 * @property string|null $selcal
 * @property \Illuminate\Support\Carbon|null $final_information_email_sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Airport|null $airportArr
 * @property-read Airport|null $airportDep
 * @property-read Event $event
 * @property-read \Illuminate\Database\Eloquent\Collection|Flight[] $flights
 * @property-read int|null $flights_count
 * @property-read string $formatted_actype
 * @property-read string $formatted_callsign
 * @property-read string $formatted_selcal
 * @property-read bool $has_received_final_information_email
 * @property-write mixed $actype
 * @property-read User|null $user
 * @method static \Database\Factories\BookingFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereAcType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCallsign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereFinalInformationEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereIsEditable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereSelcal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Booking whereUuid($value)
 * @mixin \Eloquent
 */
class Booking extends Model
{
    use HasFactory;
    use LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function getFormattedCallsignAttribute(): string
    {
        return $this->callsign ?: '-';
    }

    public function getFormattedActypeAttribute(): string
    {
        return $this->acType ?: '-';
    }

    public function getFormattedSelcalAttribute(): string
    {
        return $this->selcal ?: '-';
    }

    public function getHasReceivedFinalInformationEmailAttribute(): bool
    {
        return !empty($this->final_information_email_sent_at);
    }

    public function setCallsignAttribute($value): void
    {
        $this->attributes['callsign'] = !empty($value) ? strtoupper($value) : null;
    }

    public function setActypeAttribute($value): void
    {
        $this->attributes['acType'] = !empty($value) ? strtoupper($value) : null;
    }

    public function setSelcalAttribute($value): void
    {
        $this->attributes['selcal'] = !empty($value) ? strtoupper($value) : null;
    }

    public function airportDep(): HasOne
    {
        return $this->hasOne(Airport::class, 'id', 'dep');
    }

    public function airportArr(): HasOne
    {
        return $this->hasOne(Airport::class, 'id', 'arr');
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class)->orderBy('order_by');
    }

    public function airportCtot($orderBy, $withAbbr = true): string
    {
        if ($flight = $this->flights->where('order_by', $orderBy)->first()) {
            if ($withAbbr) {
                return "<abbr title='{$flight->airportDep->name} | [{$flight->airportDep->iata}]'>{$flight->airportDep->icao}</abbr> - <abbr title='{$flight->airportArr->name} | [{$flight->airportArr->iata}]'>{$flight->airportArr->icao}</abbr> {$flight->formattedCtot}";
            }
            return "{$flight->airportDep->icao} - {$flight->airportArr->icao} {$flight->formattedCtot}";
        }
        return '-';
    }

    public function uniqueAirports(): Collection
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
