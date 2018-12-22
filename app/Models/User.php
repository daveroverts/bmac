<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use LogsActivity;
    use Notifiable;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id', 'isAdmin'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'isAdmin' => 'boolean',
        'use_monospace_font' => 'boolean',
    ];

    protected static $logAttributes = ['*'];
    protected static $logOnlyDirty = true;

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return ucfirst($this->name_first) . ' ' . ucfirst($this->name_last);
    }

    /**
     * Get the user's full name and Vatsim ID.
     *
     * @return string
     */
    public function getPicAttribute()
    {
        return ucfirst($this->name_first) . ' ' . ucfirst($this->name_last) . ' | ' . $this->id;
    }

}
