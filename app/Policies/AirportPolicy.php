<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Airport;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirportPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin && $ability != 'delete') {
            return true;
        }
    }

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
        return $user->isAdmin && $airport->flightsDep->isEmpty() && $airport->flightsArr->isEmpty();
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
