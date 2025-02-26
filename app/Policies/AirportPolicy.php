<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Airport;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the airport.
     *
     * @return mixed
     */
    public function view(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can create airports.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the airport.
     *
     * @return mixed
     */
    public function update(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the airport.
     *
     * @return mixed
     */
    public function delete(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the airport.
     *
     * @return mixed
     */
    public function restore(User $user, Airport $airport)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the airport.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Airport $airport)
    {
        return false;
    }
}
