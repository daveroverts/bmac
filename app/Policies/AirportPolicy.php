<?php

namespace App\Policies;

use App\Models\Airport;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the airport.
     *
     * @param  User  $user
     * @param  Airport  $airport
     * @return mixed
     */
    public function view(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can create airports.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the airport.
     *
     * @param  User  $user
     * @param  Airport  $airport
     * @return mixed
     */
    public function update(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the airport.
     *
     * @param  User  $user
     * @param  Airport  $airport
     * @return mixed
     */
    public function delete(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the airport.
     *
     * @param  User  $user
     * @param  Airport  $airport
     * @return mixed
     */
    public function restore(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the airport.
     *
     * @param  User  $user
     * @param  Airport  $airport
     * @return mixed
     */
    public function forceDelete(User $user, Airport $airport)
    {
        return false;
    }
}
