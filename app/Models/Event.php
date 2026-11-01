<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property int $event_type_id
 * @property bool $is_online
 * @property bool $show_on_homepage
 * @property string $name
 * @property string $slug
 * @property string|null $image_url
 * @property string $description
 * @property string|null $dep
 * @property string|null $arr
 * @property \Illuminate\Support\Carbon $startEvent
 * @property \Illuminate\Support\Carbon $endEvent
 * @property \Illuminate\Support\Carbon $startBooking
 * @property \Illuminate\Support\Carbon $endBooking
 * @property bool $import_only
 * @property bool $uses_times
 * @property bool $multiple_bookings_allowed
 * @property bool $is_oceanic_event
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport|null $airportArr
 * @property-read \App\Models\Airport|null $airportDep
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Booking> $bookings
 * @property-read int|null $bookings_count
 * @property-read int|null $available_bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Faq> $faqs
 * @property-read int|null $faqs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EventLink> $links
 * @property-read int|null $links_count
 * @property-read \App\Models\EventType|null $type
 * @method static \Database\Factories\EventFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event upcoming()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event online()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event onHomepage()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereArr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDep($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEndBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEndEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereEventTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereImportOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereIsOceanicEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereMultipleBookingsAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereShowOnHomepage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStartBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereStartEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event whereUsesTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Event withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 */
#[\Illuminate\Database\Eloquent\Attributes\Guarded([
    'id', 'slug',
])]
#[\Illuminate\Database\Eloquent\Attributes\RouteKey('slug')]
class Event extends Model
{
    use HasFactory;
    use LogsActivity;
    use Sluggable;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    /** @return HasMany<Booking, $this> */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(EventType::class, 'event_type_id');
    }

    public function airportDep(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'dep');
    }

    public function airportArr(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'arr');
    }

    public function links(): HasMany
    {
        return $this->hasMany(EventLink::class);
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class);
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function scopeUpcoming(Builder $query): void
    {
        $query->where('endEvent', '>', now())
            ->orderBy('startEvent');
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function scopeOnline(Builder $query): void
    {
        $query->where('is_online', true);
    }

    /**
     * @param  Builder<static>  $query
     */
    protected function scopeOnHomepage(Builder $query): void
    {
        $query->where('show_on_homepage', true);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ],
        ];
    }

    public function hasOrderButtons(): bool
    {
        return in_array($this->event_type_id, [
            \App\Enums\EventType::FLYIN->value,
            \App\Enums\EventType::GROUPFLIGHT->value
        ]);
    }

    /**
     * Multi flights slots need two legs, which only the import creates.
     */
    public function allowsManualBookings(): bool
    {
        return $this->event_type_id != \App\Enums\EventType::MULTIFLIGHTS->value;
    }

    /**
     * Resolve an "H:i" slot time to the date nearest this event's window.
     */
    public function resolveSlotTime(string $time): Carbon
    {
        $startDate = $this->startEvent->copy()->startOfDay();
        $best = null;
        $bestDistance = PHP_INT_MAX;

        foreach ([0, 1, -1] as $offset) {
            $candidate = Date::createFromFormat(
                'Y-m-d H:i',
                $startDate->copy()->addDays($offset)->toDateString() . ' ' . $time
            );

            if ($candidate < $this->startEvent) {
                $distance = $this->startEvent->diffInSeconds($candidate, true);
            } elseif ($candidate > $this->endEvent) {
                $distance = $candidate->diffInSeconds($this->endEvent, true);
            } else {
                $distance = 0;
            }

            if ($distance < $bestDistance) {
                $best = $candidate;
                $bestDistance = $distance;
            }
        }

        assert($best instanceof Carbon);

        return $best;
    }

    /**
     * Resolve an "H:i" time to its first occurrence at or after $after.
     */
    public function resolveTimeAfter(string $time, CarbonInterface $after): Carbon
    {
        $candidate = Date::createFromFormat('Y-m-d H:i', $after->toDateString() . ' ' . $time);

        if ($candidate < $after) {
            $candidate->addDay();
        }

        return $candidate;
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    #[\Override]
    protected function casts(): array
    {
        return [
            'is_online' => 'boolean',
            'show_on_homepage' => 'boolean',
            'image_url' => 'string',
            'dep' => 'string',
            'arr' => 'string',
            'import_only' => 'boolean',
            'uses_times' => 'boolean',
            'multiple_bookings_allowed' => 'boolean',
            'is_oceanic_event' => 'boolean',
            'startEvent' => 'datetime',
            'endEvent' => 'datetime',
            'startBooking' => 'datetime',
            'endBooking' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}
