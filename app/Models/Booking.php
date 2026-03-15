<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

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
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking booked()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking unassigned()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Booking reserved()
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

    public const RESERVATION_TIMEOUT_MINUTES = 10;

    protected $guarded = [
        'id', 'uuid', 'status', 'selcal',
    ];

    /**
     *  Setup model event hooks
     */
    #[\Override]
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

    /**
     * @param  Builder<static>  $query
     */
    protected function scopeBooked(Builder $query): void
    {
        $query->where('status', BookingStatus::BOOKED);
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function scopeUnassigned(Builder $query): void
    {
        $query->where('status', BookingStatus::UNASSIGNED);
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function scopeReserved(Builder $query): void
    {
        $query->where('status', BookingStatus::RESERVED);
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    #[\Override]
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        if (! Str::isUuid($value)) {
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }

    protected function formattedCallsign(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): string => $attributes['callsign'] ?? '-',
        );
    }

    protected function formattedActype(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): string => $attributes['acType'] ?? '-',
        );
    }

    protected function formattedSelcal(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): string => $attributes['selcal'] ?? '-',
        );
    }

    protected function hasReceivedFinalInformationEmail(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes): bool => ! empty($attributes['final_information_email_sent_at']),
        );
    }

    protected function callsign(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => empty($value) ? null : strtoupper((string) $value),
        );
    }

    protected function acType(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => empty($value) ? null : strtoupper((string) $value),
        );
    }

    protected function selcal(): Attribute
    {
        return Attribute::make(
            set: fn (mixed $value): ?string => empty($value) ? null : strtoupper((string) $value),
        );
    }

    public function airportDep(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'dep');
    }

    public function airportArr(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'arr');
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

    public function airportCtot(int $orderBy): string
    {
        if ($flight = $this->flights->where('order_by', $orderBy)->first()) {
            return sprintf('%s - %s %s', $flight->airportDep->icao, $flight->airportArr->icao, $flight->formatted_ctot);
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

    #[\Override]
    protected function casts(): array
    {
        return [
            'status' => BookingStatus::class,
            'is_editable' => 'boolean',
            'final_information_email_sent_at' => 'datetime',
        ];
    }
}
