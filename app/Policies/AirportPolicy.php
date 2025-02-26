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
     */
    public function view(User $user, Airport $airport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create airports.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the airport.
     */
    public function update(User $user, Airport $airport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the airport.
     */
    public function delete(User $user, Airport $airport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the airport.
     */
    public function restore(User $user, Airport $airport): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the airport.
     */
    public function forceDelete(User $user, Airport $airport): bool
    {
        return false;
    }
}
