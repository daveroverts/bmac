<?php

namespace App\Models;

use Spatie\Activitylog\LogOptions;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * App\Models\Event
 *
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
 * @property-read \Illuminate\Database\Eloquent\Collection|\Spatie\Activitylog\Models\Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read \App\Models\Airport|null $airportArr
 * @property-read \App\Models\Airport|null $airportDep
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Faq[] $faqs
 * @property-read int|null $faqs_count
 * @property-read \Illuminate\Database\Eloquent\Collection|EventLink[] $links
 * @property-read int|null $links_count
 * @property-read \App\Models\EventType|null $type
 * @method static \Database\Factories\EventFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Event findSimilarSlugs(string $attribute, array $config, string $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereArr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEndEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereEventTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereImportOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereIsOceanicEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereMultipleBookingsAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereShowOnHomepage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereStartEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event whereUsesTimes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Event withUniqueSlugConstraints(\Illuminate\Database\Eloquent\Model $model, string $attribute, array $config, string $slug)
 * @mixin \Eloquent
 */
class Event extends Model
{
    use HasFactory;
    use LogsActivity;
    use Sluggable;

    protected $guarded = [
        'id', 'slug',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'startEvent',
        'endEvent',
        'startBooking',
        'endBooking',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_online' => 'boolean',
        'show_on_homepage' => 'boolean',
        'image_url' => 'string',
        'dep' => 'string',
        'arr' => 'string',
        'import_only' => 'boolean',
        'uses_times' => 'boolean',
        'multiple_bookings_allowed' => 'boolean',
        'is_oceanic_event' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnlyDirty();
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function type(): HasOne
    {
        return $this->hasOne(EventType::class, 'id', 'event_type_id');
    }

    public function airportDep(): HasOne
    {
        return $this->hasOne(Airport::class, 'id', 'dep');
    }

    public function airportArr(): HasOne
    {
        return $this->hasOne(Airport::class, 'id', 'arr');
    }

    public function links(): HasMany
    {
        return $this->hasMany(EventLink::class);
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class);
    }

    public function sluggable(): array
    {
        return [
            'slug' => [
                'source' => 'name'
            ],
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function hasOrderButtons(): bool
    {
        return in_array($this->event_type_id, [
            \App\Enums\EventType::FLYIN,
            \App\Enums\EventType::GROUPFLIGHT
        ]);
    }
}
