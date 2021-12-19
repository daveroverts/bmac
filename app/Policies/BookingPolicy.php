<?php

namespace App\Policies;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the booking.
     *
     * @param  User  $user
     * @param  Booking  $booking
     * @return mixed
     */
    public function view(User $user, Booking $booking)
    {
        return $user->id === $booking->user_id;
    }

    /**
     * Determine whether the user can create bookings.
     *
     * @param  User  $user
     * @return mixed
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Determine whether the user can update the booking.
     *
     * @param  User  $user
     * @param  Booking  $booking
     * @return mixed
     */
    public function update(User $user, Booking $booking)
    {
        return $user->id === $booking->user_id || empty($booking->user_id);
    }

    /**
     * Determine whether the user can delete the booking.
     *
     * @param  User  $user
     * @param  Booking  $booking
     * @return mixed
     */
    public function delete(User $user, Booking $booking)
    {
        return false;
    }

    /**
     * Determine whether the user can restore the booking.
     *
     * @param  User  $user
     * @param  Booking  $booking
     * @return mixed
     */
    public function restore(User $user, Booking $booking)
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the booking.
     *
     * @param  User  $user
     * @param  Booking  $booking
     * @return mixed
     */
    public function forceDelete(User $user, Booking $booking)
    {
        return false;
    }

    /**
     * Determine whether the user can cancel the booking.
     *
     * @param  User  $user
     * @param  Booking  $booking
     * @return mixed
     */
    public function cancel(User $user, Booking $booking)
    {
        return $user->id === $booking->user_id;
    }
}
