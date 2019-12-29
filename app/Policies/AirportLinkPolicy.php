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
     *
     * @param  User  $user
     * @param  AirportLink  $airportLink
     * @return mixed
     */
    public function view(User $user, AirportLink $airportLink)
    {
        return false;
    }

    /**
     * Determine whether the user can create airport links.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the airport link.
     *
     * @param  User  $user
     * @param  AirportLink  $airportLink
     * @return mixed
     */
    public function update(User $user, AirportLink $airportLink)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the airport link.
     *
     * @param  User  $user
     * @param  AirportLink  $airportLink
     * @return mixed
     */
    public function delete(User $user, AirportLink $airportLink)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the airport link.
     *
     * @param  User  $user
     * @param  AirportLink  $airportLink
     * @return mixed
     */
    public function restore(User $user, AirportLink $airportLink)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the airport link.
     *
     * @param  User  $user
     * @param  AirportLink  $airportLink
     * @return mixed
     */
    public function forceDelete(User $user, AirportLink $airportLink)
    {
        return false;
    }
}
