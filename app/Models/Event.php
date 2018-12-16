<?php

namespace App\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use Sluggable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'event_type_id', 'startEvent', 'endEvent', 'startBooking', 'endBooking', 'image_url', 'description', 'dep', 'arr', 'import_only', 'uses_times', 'multiple_bookings_allowed', 'is_oceanic_event',
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
        'image_url' => 'string',
        'dep' => 'string',
        'arr' => 'string',
        'import_only' => 'boolean',
        'uses_times' => 'boolean',
        'multiple_bookings_allowed' => 'boolean',
        'is_oceanic_event' => 'boolean',
    ];

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
        return $this->hasOne(Airport::class, 'icao', 'dep');
    }

    public function airportArr()
    {
        return $this->hasOne(Airport::class, 'icao', 'arr');
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
}
