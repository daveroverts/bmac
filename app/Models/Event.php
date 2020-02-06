<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;

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
 * @property string $dep
 * @property string $arr
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
 * @property-read \App\Models\Airport $airportArr
 * @property-read \App\Models\Airport $airportDep
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Booking[] $bookings
 * @property-read int|null $bookings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Faq[] $faqs
 * @property-read int|null $faqs_count
 * @property-read \App\Models\EventType $type
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event findSimilarSlugs($attribute, $config, $slug)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereArr($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereDep($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereEndBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereEndEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereEventTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereImportOnly($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereIsOceanicEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereMultipleBookingsAllowed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereShowOnHomepage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereStartBooking($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereStartEvent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Event whereUsesTimes($value)
 * @mixin \Eloquent
 */
class Event extends Model
{
    use LogsActivity;
    use Sluggable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
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
     * @var array
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

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function type()
    {
        return $this->hasOne(EventType::class, 'id', 'event_type_id');
    }

    public function airportDep()
    {
        return $this->hasOne(Airport::class, 'id', 'dep');
    }

    public function airportArr()
    {
        return $this->hasOne(Airport::class, 'id', 'arr');
    }

    public function faqs()
    {
        return $this->belongsToMany(Faq::class);
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ],
        ];
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function hasOrderButtons()
    {
        return in_array($this->event_type_id, [
            \App\Enums\EventType::FLYIN,
            \App\Enums\EventType::GROUPFLIGHT
        ]);
    }
}
