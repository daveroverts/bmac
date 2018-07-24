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
        'name', 'startEvent', 'endEvent', 'startBooking', 'endBooking', 'description', 'sendFeedbackForm', 'formSent'
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
        'sendFeedbackForm',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'formSent' => 'boolean',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function sluggable()
    {
        return [
            'slug' => [
                'source' => 'name'
            ],
        ];
    }

}
