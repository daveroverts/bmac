<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AirportLink;
use Illuminate\Auth\Access\HandlesAuthorization;

class AirportLinkPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the airport link.
     */
    public function view(User $user, AirportLink $airportLink): bool
    {
        return false;
    }

    /**
     * Determine whether the user can create airport links.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the airport link.
     */
    public function update(User $user, AirportLink $airportLink): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the airport link.
     */
    public function delete(User $user, AirportLink $airportLink): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the airport link.
     */
    public function restore(User $user, AirportLink $airportLink): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the airport link.
     */
    public function forceDelete(User $user, AirportLink $airportLink): bool
    {
        return false;
    }
}
