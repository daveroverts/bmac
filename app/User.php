<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bmac_users';

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

    public function isAdmin(){
        if ($this->isAdmin == 1){
            return true;
        }
        else return false;
    }

    public function bookingReserved() {
        return $this->hasOne(Booking::class);
    }

    public function bookingBooked() {
        return $this->hasOne(Booking::class);
    }


}
