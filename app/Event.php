<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

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

    public function bookings() {
        return $this->belongsTo(Booking::class);
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
