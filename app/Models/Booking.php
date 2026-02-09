<?php

namespace App\Models;

use App\Enums\BookingStatus;
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
 * @property int $id
 * @property string|null $uuid
 * @property int $event_id
 * @property BookingStatus $status
 * @property bool $is_editable
 * @property int|null $user_id
 * @property string|null $callsign
 * @property string|null $acType
 * @property string|null $selcal
 * @property \Illuminate\Support\Carbon|null $final_information_email_sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport|null $airportArr
 * @property-read \App\Models\Airport|null $airportDep
 * @property-read \App\Models\Event $event
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Flight> $flights
 * @property-read int|null $flights_count
 * @property-read string $formatted_actype
 * @property-read string $formatted_callsign
 * @property-read string $formatted_selcal
 * @property-read bool $has_received_final_information_email
 * @property-write mixed $actype
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\BookingFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereAcType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCallsign($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereEventId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereFinalInformationEmailSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereIsEditable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereSelcal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking whereUuid($value)
 * @mixin \Eloquent
 */
class Booking extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $guarded = [
        'id', 'uuid', 'status', 'selcal',
    ];

    /**
     *  Setup model event hooks
     */
    protected static function boot(): void
    {
        parent::boot();
        self::creating(function ($model): void {
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

    protected function getFormattedCallsignAttribute(): string
    {
        return $this->callsign ?: '-';
    }

    protected function getFormattedActypeAttribute(): string
    {
        return $this->acType ?: '-';
    }

    protected function getFormattedSelcalAttribute(): string
    {
        return $this->selcal ?: '-';
    }

    protected function getHasReceivedFinalInformationEmailAttribute(): bool
    {
        return !empty($this->final_information_email_sent_at);
    }

    protected function setCallsignAttribute($value): void
    {
        $this->attributes['callsign'] = empty($value) ? null : strtoupper((string) $value);
    }

    protected function setActypeAttribute($value): void
    {
        $this->attributes['acType'] = empty($value) ? null : strtoupper((string) $value);
    }

    protected function setSelcalAttribute($value): void
    {
        $this->attributes['selcal'] = empty($value) ? null : strtoupper((string) $value);
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
                return sprintf("<abbr title='%s | [%s]'>%s</abbr> - <abbr title='%s | [%s]'>%s</abbr> %s", $flight->airportDep->name, $flight->airportDep->iata, $flight->airportDep->icao, $flight->airportArr->name, $flight->airportArr->iata, $flight->airportArr->icao, $flight->formattedCtot);
            }

            return sprintf('%s - %s %s', $flight->airportDep->icao, $flight->airportArr->icao, $flight->formattedCtot);
        }

        return '-';
    }

    public function uniqueAirports(): Collection
    {
        $airports = collect();

        /** @var \Illuminate\Database\Eloquent\Collection<int, Flight> $flights */
        $flights = $this->flights()->get();

        $flights->each(function (Flight $flight) use ($airports): void {
            $airports->push($flight->airportDep);
            $airports->push($flight->airportArr);
        });

        return $airports->unique();
    }

    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'is_editable' => 'boolean',
            'has_already_received_final_information_email' => 'boolean',
            'final_information_email_sent_at' => 'datetime',
        ];
    }
}
