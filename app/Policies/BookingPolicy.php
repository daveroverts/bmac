<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Booking;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the booking.
     *
     * @return mixed
     */
    public function view(User $user, Booking $booking)
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can create bookings.
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the booking.
     *
     * @return mixed
     */
    public function update(User $user, Booking $booking)
    {
        return $user->id === $booking->user_id || empty($booking->user_id);
    }

    /**
     * Determine whether the user can delete the booking.
     *
     * @return mixed
     */
    public function delete(User $user, Booking $booking)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the booking.
     *
     * @return mixed
     */
    public function restore(User $user, Booking $booking)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the booking.
     *
     * @return mixed
     */
    public function forceDelete(User $user, Booking $booking)
    {
        return false;
    }

    /**
     * Determine whether the user can cancel the booking.
     *
     * @return mixed
     */
    public function cancel(User $user, Booking $booking)
    {
        return $user->id === $booking->user_id;
    }
}
