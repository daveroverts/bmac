<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'country', 'region', 'division', 'subdivision',
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
    ];

    public function bookingReserved() {
        return $this->hasOne(Booking::class);
    }

    public function bookingBooked() {
        return $this->hasOne(Booking::class);
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute() {
        return ucfirst($this->name_first) . ' ' . ucfirst($this->name_last);
    }

    /**
     * Get the user's full name and Vatsim ID.
     *
     * @return string
     */
    public function getPicAttribute() {
        return ucfirst($this->name_first) . ' ' . ucfirst($this->name_last) . ' | ' . $this->id;
    }

}
