<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name_first', 'name_last', 'email', 'country', 'region', 'division', 'subdivision', 'airport_view', 'use_monospace_font',
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
        'is_admin' => 'boolean',
        'use_monospace_font' => 'boolean',
    ];

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
