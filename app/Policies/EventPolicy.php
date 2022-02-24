<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Event;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->isAdmin) {
            return true;
        }
    }

    /**
     * Determine whether the user can view the event.
     *
     * @param  User  $user
     * @param  Event  $event
     * @return mixed
     */
    public function view(?User $user, Event $event)
    {
        return true;
    }

    /**
     * Determine whether the user can create events.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the event.
     *
     * @param  User  $user
     * @param  Event  $event
     * @return mixed
     */
    public function update(User $user, Event $event)
    {
        return false;
    }

    /**
     * Determine whether the user can delete the event.
     *
     * @param  User  $user
     * @param  Event  $event
     * @return mixed
     */
    public function delete(User $user, Event $event)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the event.
     *
     * @param  User  $user
     * @param  Event  $event
     * @return mixed
     */
    public function restore(User $user, Event $event)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the event.
     *
     * @param  User  $user
     * @param  Event  $event
     * @return mixed
     */
    public function forceDelete(User $user, Event $event)
    {
        return false;
    }
}
